<template>
    <div class="card" style="border-radius: 0">
        <img v-if="userInfo.timeline_picture" :src="userInfo.timeline_picture" alt="Cover" class="card-img-top" height="350">

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

    <nav class="navbar navbar-expand-lg bg-body-tertiary" style="border: 1px solid #ccc">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <Link class="nav-link" :class="route().current() === 'profile' ? 'active' : ''" aria-current="page" :href="route('profile')">My Profile</Link>
                    </li>

                    <li class="nav-item">
                        <Link class="nav-link" :class="route().current() === 'tweet' ? 'active' : ''" aria-current="page" :href="route('tweet')">My Tweets</Link>
                    </li>

                    <li class="nav-item">
                        <Link class="nav-link" :class="route().current() === 'follower' ? 'active' : ''" aria-current="page" :href="route('follower')">Followers</Link>
                    </li>

                    <li class="nav-item">
                        <Link class="nav-link" :class="route().current() === 'following' ? 'active' : ''" aria-current="page" :href="route('following')">Following</Link>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup>
    import {Link} from '@inertiajs/vue3'

    const props = defineProps({
        userInfo: {
            type: Array,
        }
    })
</script>
