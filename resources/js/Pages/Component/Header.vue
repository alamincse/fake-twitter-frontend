<template>
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
                </ul>

                <h6 style="margin-right: 4px">Welcome {{ userInfo.name }}</h6>
                <button @click="logout" class="btn btn-sm btn-primary">Logout</button>
            </div>
        </div>
    </nav>
</template>

<script setup>
    import {Link, router} from '@inertiajs/vue3'

    const props = defineProps({
        userInfo: {
            type: Array,
        }
    })

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

</script>
