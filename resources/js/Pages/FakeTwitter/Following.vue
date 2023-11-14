<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-12">
                <Header :userInfo="userInfo"/>

                <ProfileInfo :userInfo="userInfo"/>

                <div class="container" style="border: 1px solid #ccc;border-radius: 4px;">
                    <div class="row">
                        <div class="col-md-8 offset-md-2 py-4">
                            <template v-if="followingInfo?.following?.length !== 0">
                                <h5>Your following list</h5>
                                <div class="card mt-4">
                                    <ul class="list-group list-group-flush">
                                        <li v-for="(item, index) in followingInfo.following" :key="index" class="list-group-item">
                                            <div class="d-flex">
                                                <img v-if="item.profile_picture !== ''" :src="item.profile_picture" height="40" width="40" alt="profile" class="mr-3 rounded-circle">
                                                <img v-else src="assets/images/male.png" height="40" width="40" alt="profile" class="mr-3 rounded-circle">

                                                <div style="margin-left: 10px">
                                                    <h5 class="mb-0">{{ item.participant.name }}</h5>
                                                    <small class="text-muted">{{ item.participant.date }}</small>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <template v-else>
                                <div class="alert alert-primary" role="alert">
                                    <p>You're not following anyone yet!</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {router} from '@inertiajs/vue3'
    import ProfileInfo from "../Component/ProfileInfo.vue";
    import Header from "../Component/Header.vue";
    import {onMounted, ref} from "vue";


    const userInfo = ref('');
    const followingInfo = ref('');

    const accessToken = localStorage.getItem('accessToken');

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
        });
    };

    // const doSearch = () => {
    //     axios({
    //         method: 'post',
    //         url: 'v2/search',
    //         headers: { 'Authorization': 'Bearer ' + accessToken },
    //         data: searchForm,
    //     })
    //     .then(res => {
    //         searchInfo.value = res.data.data;
    //     })
    //     .catch(err => {});
    // }

    const getFollowing = () => {
        axios({
            method: 'post',
            url: 'v2/following',
            headers: { 'Authorization': 'Bearer ' + accessToken }
        })
        .then(res => {
            followingInfo.value = res.data.data;
        });
    };

    onMounted(() => {
        if (! accessToken) {
            router.get('/');
        }

        profile();
        getFollowing();
    });
</script>

