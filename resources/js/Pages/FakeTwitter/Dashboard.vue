<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-12">
                <nav class="navbar navbar-expand-lg bg-body-tertiary" style="background-color: #e3f2fd;">
                    <div class="container-fluid">
                        <Link :href="route('dashboard')" class="text-decoration-none">
                            <h4>Fake Twitter</h4>
                        </Link>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="margin-left: 10px">
                                <li class="nav-item">
                                    <Link class="nav-link" :href="route('profile')"><strong>Profile</strong></Link>
                                </li>
                                <form @submit.prevent="doSearch" class="d-flex">
                                    <input v-model="searchForm.email" type="text" class="form-control me-2" placeholder="Search with email" required>
                                    <button class="btn btn-outline-success" type="submit">Search</button>
                                </form>
                            </ul>


                            <h6 style="margin-right: 4px">Welcome {{ userInfo.name }}</h6>
                            <button @click="logout" class="btn btn-sm btn-primary">Logout</button>
                        </div>
                    </div>
                </nav>


                <div class="container" style="border: 1px solid #ccc;border-radius: 4px;">
                    <div class="row mt-2">
                        <div class="col-md-8">
                            <template v-if="searchInfo && searchInfo.participant !== null">
                                <div class="card mt-4">
                                    <ul class="list-group list-group-flush">
                                        <li v-for="(item, index) in searchInfo.tweets" :key="index" class="list-group-item">
                                            <div class="d-flex">
                                                <img v-if="item.profile_picture !== ''" :src="item.profile_picture" alt="profile" height="40" width="40" class="mr-3 rounded-circle">
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
                                                    @click="doLike(item.id)"
                                                    class="btn btn-outline-primary"
                                                >
                                                    <span v-if="item.total_likes">{{ item.total_likes }}</span>
                                                    <span> Like</span>
                                                </button>

                                                <button
                                                    v-if="item.like_status === 'Yes'"
                                                    @click="doUnlike(item.id)"
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
    <!--                            <h5 v-if="successMessage" style="color:green;">{{ successMessage }}</h5>-->

                                <form @submit.prevent="postTweet">
                                    <div>
                                        <textarea v-model="form.tweet" class="form-control" placeholder="What's on your mind ?" rows="3"></textarea>
                                        <span v-if="form.errors.tweet" style="color:chocolate;">{{ form.errors.tweet }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary">Tweet</button>
                                    </div>
                                </form>


                                <template v-if="tweetInfo.length !== 0">
                                    <div class="card mt-4">
                                        <ul class="list-group list-group-flush">
                                            <li v-for="(item, index) in tweetInfo" :key="index" class="list-group-item">
                                                <div class="d-flex">
                                                    <img v-if="item.profile_picture !== ''" :src="item.profile_picture" height="40" width="40" alt="Avatar" class="mr-3 rounded-circle">
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
                                                        @click="doLike(item.id)"
                                                        class="btn btn-outline-primary"
                                                    >
                                                        <span v-if="item.total_likes">{{ item.total_likes }}</span>
                                                        <span> Like</span>
                                                    </button>

                                                    <button
                                                        v-if="item.like_status === 'Yes'"
                                                        @click="doUnlike(item.id)"
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
                            </template>
                        </div>


                        <div class="col-md-4">
                            <div class="px-2 py-2">
                                <template v-if="searchInfo && searchInfo.participant !== null">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <img v-if="searchInfo.participant.profile_picture" :src="searchInfo.participant.profile_picture"  alt="Photo" class="rounded-circle mb-3" height="150" width="150">
                                            <img v-else src="assets/images/male.png" alt="Photos" class="rounded-circle mb-3" height="150" width="150">

                                            <h5 class="card-title">{{ searchInfo.participant.name }}</h5>
                                            <p class="card-text text-muted">{{ searchInfo.participant.email }}</p>
                                            <p class="card-text">Join Date: {{ searchInfo.participant.join_date }}</p>
                                            <p class="card-text">
                                                <span class="btn btn-info"><strong>Followers {{ searchInfo.participant.total_follower }}</strong></span>

                                                <template v-if="searchForm.email !== searchInfo.participant.email">
                                                    <button
                                                        v-if="searchInfo.participant.is_following === 'No'"
                                                        @click="doFollow(searchInfo.participant.id)"
                                                        class="btn btn-primary"
                                                        style="margin-left: 5px"
                                                    >Follow</button>

                                                    <button
                                                        v-if="searchInfo.participant.is_following === 'Yes'"
                                                        @click="doUnfollow(searchInfo.participant.id)"
                                                        class="btn btn-success"
                                                        style="margin-left: 5px"
                                                    >Following</button>
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <img v-if="userInfo.profile_picture" :src="userInfo.profile_picture" alt="Photo" class="rounded-circle mb-3" height="150" width="150">
                                            <img v-else src="assets/images/male.png" alt="Photo" class="rounded-circle mb-3" height="150" width="150">

                                            <h5 class="card-title">{{ userInfo.name }}</h5>
                                            <p class="card-text text-muted">{{ userInfo.email }}</p>
                                            <p class="card-text">Join Date: {{ userInfo.join_date }}</p>
                                            <p class="card-text">
                                                <span class="btn btn-success"><strong>Followers {{ userInfo.total_follower }}</strong></span>
                                                <span class="btn btn-danger" style="margin-left: 5px;"><strong>Following {{ userInfo.following_count }}</strong></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {router, Link, useForm} from '@inertiajs/vue3'
    import {onMounted, ref} from "vue";

    const successMessage = ref('');

    const form = useForm({
        tweet: '',
    });

    const searchForm = useForm({
        email: '',
    });

    const userInfo = ref('');
    const tweetInfo = ref('');
    const searchInfo = ref('');

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

    const postTweet = () => {
        axios({
            method: 'post',
            url: 'v2/tweet',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: form,
        })
        .then(res => {
            successMessage.value = res.data.message;
            form.clearErrors();
            form.reset();

            getTweets();

            // router.visit('/dashboard', {
            //     only: ['getTweets'],
            // })
        })
        .catch(err => {
            let errMessage = err.response.data.message;

            if (errMessage.tweet) {
                form.setError('tweet', errMessage.tweet[0]);
            }
        });
    }

    const doSearch = () => {
        axios({
            method: 'post',
            url: 'v2/search',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: searchForm,
        })
        .then(res => {
            searchInfo.value = res.data.data;
            // console.log(res.data.data);
        })
        .catch(err => {});
    }

    const getTweets = () => {
        axios({
            method: 'get',
            url: 'v2/tweet',
            headers: { 'Authorization': 'Bearer ' + accessToken }
        })
        .then(res => {
            tweetInfo.value = res.data.data;
        });
    }

    const doLike = (itemId) => {
        axios({
            method: 'post',
            url: 'v2/like',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: {'tweet_id': itemId},
        })
        .then(res => {
            // console.log(res);
            getTweets();
            doSearch();
        });
    }

    const doUnlike = (itemId) => {
        axios({
            method: 'post',
            url: 'v2/unlike',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: {'tweet_id': itemId},
        })
        .then(res => {
            getTweets();
            doSearch();
        });
    }

    const doFollow = (participantId) => {
        axios({
            method: 'post',
            url: 'v2/follow',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: {'participant_id': participantId},
        })
            .then(res => {
                doSearch();
            });
    }

    const doUnfollow = (participantId) => {
        axios({
            method: 'post',
            url: 'v2/unfollow',
            headers: { 'Authorization': 'Bearer ' + accessToken },
            data: {'participant_id': participantId},
        })
            .then(res => {
                doSearch();
            });
    }

    onMounted(() => {
        if (! accessToken) {
            router.get('/');
        }

        profile();
        getTweets();
        // doSearch();
    });
</script>

