<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-4 px-4 py-4" style="border: 1px solid #ccc;border-radius: 4px;margin-top: 50px;">
                <h3 class="mb-4 text-center">Register your account</h3>
                <h5 v-if="successMessage" style="color:green;">{{ successMessage }}</h5>

                <form @submit.prevent="submit">
                    <div>
                        <label for="name" class="form-label">Name</label>
                        <input type="text" v-model="form.name" class="form-control" id="name">
                        <span v-if="form.errors.name" style="color:chocolate;">{{ form.errors.name }}</span>
                    </div>

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
                        <button type="submit" class="btn btn-primary">Register</button>
                        <Link :href="route('login')" class="btn btn-info" style="margin-left: 4px">Already have an account ?</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {Link, router, useForm} from '@inertiajs/vue3'
    import {onMounted, ref} from "vue";

    const successMessage = ref('');

    const form = useForm({
        name: '',
        email: '',
        password: '',
    });

    const submit = () => {
        axios.post('v2/register', form)
            .then(res => {
                // console.log(res);
                successMessage.value = res.data.message;
                form.clearErrors();
                form.reset();
            })
            .catch(err => {
                let errMessage = err.response.data.message;

                if (errMessage.name) {
                    form.setError('name', errMessage.name[0]);
                } else if (errMessage.email) {
                    form.setError('email', errMessage.email[0]);
                } else if (errMessage.password) {
                    form.setError('password', errMessage.password[0]);
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
