<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-12">
                <Header :userInfo="userInfo"/>

                <ProfileInfo :userInfo="userInfo"/>

                <div class="container" style="border: 1px solid #ccc;border-radius: 4px;">
                    <div class="row mt-2">
                        <div class="col-md-8 offset-md-2">
                            <template v-if="tweetInfo.length !== 0">
                                <div class="card py-4">
                                    <ul class="list-group list-group-flush">
                                        <li v-for="(item, index) in tweetInfo" :key="index" class="list-group-item">
                                            <div class="d-flex">
                                                <img v-if="item.profile_picture !== ''" :src="item.profile_picture" height="40" width="40" alt="profile" class="mr-3 rounded-circle">
                                                <img v-else src="assets/images/male.png" height="40" width="40" alt="Avatar" class="mr-3 rounded-circle">
                                                <div style="margin-left: 10px">
                                                    <h5 class="mb-0">{{ item.participant.name }}</h5>
                                                    <small class="text-muted">{{ item.date }}</small>
                                                </div>
                                            </div>
                                            <p class="mt-3">{{ item.tweet }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button
                                                    v-if="item.like_status === 'No'"
                                                    class="btn btn-outline-primary"
                                                >
                                                    <span v-if="item.total_likes">{{ item.total_likes }}</span>
                                                    <span> Like</span>
                                                </button>

                                                <button
                                                    v-if="item.like_status === 'Yes'"
                                                    class="btn btn-outline-success"
                                                >
                                                    <span v-if="item.total_likes">{{ item.total_likes }}</span>
                                                    <span> Liked</span>
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <template v-else>
                                <div class="alert alert-primary" role="alert">
                                    <p>You don't have any tweet!</p>
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
    const tweetInfo = ref('');

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

    const getMyTweets = () => {
        axios({
            method: 'get',
            url: 'v2/my-tweet',
            headers: { 'Authorization': 'Bearer ' + accessToken }
        })
        .then(res => {
            tweetInfo.value = res.data.data;
        });
    }

    onMounted(() => {
        if (! accessToken) {
            router.get('/');
        }

        profile();
        getMyTweets();
    });
</script>
