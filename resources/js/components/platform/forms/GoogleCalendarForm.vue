<template>
</template>

<script>
    import EventBus from "../../../event-bus";

    export default {
        name: "GoogleCalendarForm",

        data() {
            return {
                secret: {},

                empty_secret: null
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

