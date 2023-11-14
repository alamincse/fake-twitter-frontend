<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-12">
                <Header :userInfo="userInfo"/>

                <ProfileInfo :userInfo="userInfo"/>

                <div class="container" style="border: 1px solid #ccc;border-radius: 4px;">
                    <div class="row mt-2">
                        <div class="col-md-8 offset-md-2">
                            <h5 v-if="successMessage" style="color:green;">{{ successMessage }}</h5>

                            <form @submit.prevent="submit" enctype="multipart/form-data">
                                <div>
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" v-model="form.name" class="form-control" id="name">
                                    <span v-if="form.errors.name" style="color:chocolate;">{{ form.errors.name }}</span>
                                </div>

                                <div class="mt-2">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" v-model="form.email" class="form-control" id="email">
                                    <span v-if="form.errors.email" style="color:chocolate;">{{ form.errors.email }}</span>
                                </div>

                                <div class="mt-2">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" v-model="form.password" class="form-control" id="password">
                                    <span v-if="form.errors.password" style="color:chocolate;">{{ form.errors.password }}</span>
                                </div>

                                <div class="mt-2">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea v-model="form.address" class="form-control" rows="3" id="address"></textarea>
                                </div>

                                <div class="mt-3">
                                    <label for="picture" class="form-label">Profile Picture</label>
                                    <input type="file" @change="handleProfilePictureChange" class="form-control" id="picture"/>
                                    <span v-if="form.errors.picture" style="color:chocolate;">{{ form.errors.picture }}</span>
                                </div>

                                <div class="mt-3">
                                    <label for="timeline-picture" class="form-label">Cover Picture</label>
                                    <input type="file" @change="handleTimelinePictureChange" class="form-control" id="timeline-picture"/>
                                    <span v-if="form.errors.timeline_picture" style="color:chocolate;">{{ form.errors.timeline_picture }}</span>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>

                            <div class="mt-3 pb-5">
                                <button @click="generateNewJWTToken()" class="btn btn-success">Lost or expired your jwt token ? Click to generate new one!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {router, useForm} from '@inertiajs/vue3'
    import ProfileInfo from "../Component/ProfileInfo.vue";
    import Header from "../Component/Header.vue";
    import {onMounted, ref} from "vue";

    const userInfo = ref('');

    const successMessage = ref('');

    const form = useForm({
        name: '',
        email: '',
        password: '',
        address: '',
        picture: '',
        timeline_picture: '',
    });

    const accessToken = localStorage.getItem('accessToken');

    const setCookie = (name, value, days) => {
        const expires = new Date();

        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));

        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }

    const deleteCookie = (name) => {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    }

    const logout = () => {
        axios.defaults.headers.common = {'Authorization': `bearer ` + accessToken}

        axios.post('v2/logout')
            .then(res => {
                localStorage.removeItem('accessToken');

                deleteCookie('fake_twitter_cookie');

                router.get('/');
            })
            .catch(err => {});
    };

    const profile = () => {
        axios({
            method: 'post',
            url: 'v2/profile',
            headers: { 'Authorization': 'Bearer ' + accessToken }
        })
        .then(res => {
            userInfo.value = res.data.data;

            // show data into the form
            form.name = userInfo.value?.name;
            form.email = userInfo.value?.email;
            form.address = userInfo.value?.address;
        });
    };

    const submit = () => {
        axios({
            method: 'post',
            url: 'v2/profile-update',
            headers: {
                'Authorization': 'Bearer ' + accessToken,
                'content-type': 'multipart/form-data'
            },
            data: form,
        })
        .then(res => {
            successMessage.value = res.data.message;
            form.clearErrors();
            form.reset();

            profile();
        })
        .catch(err => {
            let errMessage = err.response.data.message;

            if (errMessage.name) {
                form.setError('name', errMessage.name[0]);
            } else if (errMessage.email) {
                form.setError('email', errMessage.email[0]);
            } else if (errMessage.password) {
                form.setError('password', errMessage.password[0]);
            } else if (errMessage.picture) {
                form.setError('picture', errMessage.picture[0]);
            } else if (errMessage.timeline_picture) {
                form.setError('timeline_picture', errMessage.timeline_picture[0]);
            }
        });
    };

    const handleProfilePictureChange = (event) => {
        const file = event.target.files[0];

        if (file) {
            form.picture = file;
        } else {
            form.picture = null;
        }
    }

    const handleTimelinePictureChange = (event) => {
        const file = event.target.files[0];

        if (file) {
            form.timeline_picture = file;
        } else {
            form.timeline_picture = null;
        }
    }

    const generateNewJWTToken = () => {
        axios({
            method: 'post',
            url: 'v2/refresh-token',
            headers: {
                'Authorization': 'Bearer ' + accessToken,
            },
        })
        .then(res => {
            successMessage.value = res.data.message;

            const accessToken = res.data.data.access_token;

            localStorage.setItem('accessToken', accessToken);

            setCookie('fake_twitter_cookie', accessToken, 1);
        })
        .catch(err => {});
    }

    onMounted(() => {
        if (! accessToken) {
            router.get('/');
        }

        profile();
    });
</script>

