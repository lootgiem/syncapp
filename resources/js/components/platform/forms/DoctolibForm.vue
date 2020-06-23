<template>
    <div>
        <div class="mt-3">
            <div>Username :</div>
            <b-form-input v-model="secret.username" placeholder="Username"></b-form-input>
        </div>
        <div class="mt-3">
            <div>Password :</div>
            <b-form-input v-model="secret.password" placeholder="Password"></b-form-input>
        </div>
    </div>
</template>

<script>
    import EventBus from "../../../event-bus";

    export default {
        name: "DoctolibForm",

        data() {
            return {
                secret: {
                    username : null,
                    password : null,
                },

                empty_secret : null
            }
        },

        mounted() {
            var self = this
            this.empty_secret = this.duplicate(this.secret);

            EventBus.$on('getSecret' + this.$options.name, function () {
                self.sendSecret()
            });

            EventBus.$on('resetSecret', function () {
                self.reset()
            });
        },

        methods: {
            reset(){
                this.secret = this.duplicate(this.empty_secret);
            },
            sendSecret() {
                EventBus.$emit('secretFieldsUpdate', this.secret);
            },
        }
    }
</script>
