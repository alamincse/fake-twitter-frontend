<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-4 px-4 py-4" style="border: 1px solid #ccc;border-radius: 4px;margin-top: 50px;">
                <h3 class="mb-4 text-center">Login to your account</h3>
                <form @submit.prevent="submit">
                    <div>
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
                        <button type="submit" class="btn btn-primary">Login</button>
                        <Link :href="route('register')" class="btn btn-info" style="margin-left: 4px">No Account ?</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {Link, router, useForm} from '@inertiajs/vue3'
    import {onMounted} from "vue";

    const form = useForm({
        email: '',
        password: '',
    });

    const setCookie = (name, value, days) => {
        const expires = new Date();

        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));

        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }

    const submit = () => {
        axios.post('v2/login', form)
            .then(res => {
                const accessToken = res.data.data.access_token;

                localStorage.setItem('accessToken', accessToken);

                setCookie('fake_twitter_cookie', accessToken, 1);

                router.get('/dashboard');
            })
            .catch(err => {
                let errMessage = err.response.data.message;

                if (errMessage.email) {
                    form.setError('email', errMessage.email[0]);
                } else if (errMessage.password) {
                    form.setError('password', errMessage.password[0]);
                } else if (errMessage) {
                    form.setError('email', errMessage);
                    form.clearErrors('password');
                }
            });
    };

    onMounted(() => {
        let accessToken = localStorage.getItem('accessToken');

        if (accessToken) {
            router.get('/dashboard');
        }
    });
</script>
