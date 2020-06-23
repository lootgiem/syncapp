<style scoped>
    .action-link {
        cursor: pointer;
    }
</style>

<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Synchronized Platforms
                    </span>

                    <a class="action-link" tabindex="-1" @click="addCredential">
                        Add New Platform
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Current Clients -->
                <p class="mb-0" v-if="credentials.length === 0">
                    You have not synchronized any platforms.
                </p>

                <table class="table table-borderless mb-0" v-if="credentials.length > 0">
                    <thead>
                        <tr>
                            <th>Credential Name</th>
                            <th>Platform</th>
                            <th>Synchronized</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="credential in credentials">
                            <td style="vertical-align: middle;">
                                {{ credential.name }}
                            </td>
                            <td style="vertical-align: middle;">
                                {{ credential.platform.readable_name }}
                            </td>
                            <td style="vertical-align: middle;">
                                {{ credential.synchronized ? credential.redirect ?  "Connection required" : "Yes" :
                                "No" }}
                            </td>
                            <td  style="vertical-align: middle;">
                                <a v-if="credential.redirect" :href="credential.redirect"
                                   class="btn btn-dark btn active" role="button"
                                   aria-pressed="true">Connect</a>

                            </td>
                            <td style="vertical-align: middle;">
                                <a class="action-link" tabindex="-1" @click="editCredential(credential)">
                                    Edit
                                </a>
                            </td>
                            <td style="vertical-align: middle;">
                                <a class="action-link text-danger" @click="destroy(credential)">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <credential-modal></credential-modal>
    </div>
</template>

<script>
    import EventBus from '../../event-bus';
    import CredentialModal from './CredentialModal.vue'

    export default {
        components: {
            'credential-modal' : CredentialModal,
        },

        data() {
            return {
                credentials : []
            }
        },

        mounted() {
            var self = this
            this.prepareComponent();

            EventBus.$on('credentialChange', function () {
                self.getCredential()
            });
        },

        methods: {
            prepareComponent() {
                this.getCredential();
            },

            getCredential() {
                axios.get('/credential')
                    .then(response => {
                        this.credentials = response.data.data
                    });
            },

            addCredential () {
                EventBus.$emit('addCredential');
            },

            editCredential (credential) {
                EventBus.$emit('editCredential', credential);
            },

            destroy(credential) {
                axios.delete('/credential/' + credential.id)
                    .then(response => {
                        this.getCredential()
                    });
            }
        }
    }
</script>

