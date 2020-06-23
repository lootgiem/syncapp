<template>
    <div class="modal fade" id="modal-platform" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{ is_edit ? 'Edit ' + form.name : 'Add New Platform'}}
                    </h4>

                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" @click="reset()">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-danger" v-if="form.errors.length > 0">
                        <ul class="pl-3 mb-0">
                            <li v-for="error in form.errors">
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <div v-if="!is_edit" class="mb-3">
                        <div>Select a platform to synchronize :</div>
                        <b-form-select v-model="form.platform_id" @change="setPlatformName">
                            <b-form-select-option :value="null" disabled selected>-- Please select an option --
                            </b-form-select-option>
                            <b-form-select-option v-for="platform in platforms_available"
                                                  :value="platform.id">
                                {{ platform.readable_name }}
                            </b-form-select-option>
                        </b-form-select>
                    </div>

                    <div v-if="selected_platform_name !== null">
                        <div>Name to identify this platform :</div>
                        <b-form-input v-model="form.name" placeholder="Name"></b-form-input>
                    </div>

                    <doctolib-form v-if="selected_platform_name === 'Doctolib'"></doctolib-form>

                    <google-calendar-form v-if="selected_platform_name === 'GoogleCalendar'"></google-calendar-form>

                    <div v-if="selected_platform_name !== null">
                        <div class="mt-3">
                            <b-form-checkbox id="synchronized" v-model="form.synchronized" name="synchronized" value="1"
                                             unchecked-value="0">
                                Check if you want to synchronize this platform automatically.
                            </b-form-checkbox>
                        </div>
                    </div>

                </div>

                <!-- Modal Actions -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="reset()">Close
                    </button>
                    <button v-if="selected_platform_name !== null" type="button" class="btn btn-primary" @click="save()">
                        {{ is_edit ? 'Save' : 'Add'}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DoctolibForm from './Forms/DoctolibForm.vue'
    import GoogleCalendarForm from './Forms/GoogleCalendarForm.vue'
    import EventBus from '../../event-bus';

    export default {
        components: {
            'doctolib-form': DoctolibForm,
            'google-calendar-form': GoogleCalendarForm,
        },

        name: "CredentialModal",

        data() {
            return {
                platforms_available: null,

                selected_platform_name: null,

                is_edit: false,

                form: {
                    errors: [],
                    id: null,
                    platform_id: null,
                    name: null,
                    synchronized: 1,
                    secret: null
                },

                empty_form : null
            }
        },

        mounted() {
            var self = this
            this.empty_form = this.duplicate(this.form);
            this.getAvailablePlatforms();

            EventBus.$on('addCredential', function () {
                self.is_edit = false;
                $('#modal-platform').modal('show');
            });

            EventBus.$on('editCredential', function (credential) {
                self.is_edit = true;
                self.edit(credential);
                self.setPlatformName();
                $('#modal-platform').modal('show');
            });

            EventBus.$on('secretFieldsUpdate', function (secret) {
                self.form.secret = secret;
            });
        },

        methods: {
            getAvailablePlatforms() {
                axios.get('/api/platforms')
                    .then(response => {
                        this.platforms_available = response.data.data
                    });
            },

            setPlatformName() {
                let platform = this.findObjectInArrayByProperty(this.platforms_available, 'id', this.form.platform_id)
                this.selected_platform_name = platform.name
            },

            reset() {
                this.is_edit = false
                this.selected_platform_name = null
                this.form = this.duplicate(this.empty_form)
                $('#modal-add-platform select').prop('selectedIndex', 0);
                EventBus.$emit('resetSecret');
            },

            save() {
                this.getChildCredential()

                if (this.is_edit) {
                    this.update()
                } else {
                    this.store()
                }
            },

            store() {
                this.persistCredential(
                    'post',
                    '/credential',
                    this.form
                );
            },

            edit(credential) {
                this.selected_platform_name = credential.name
                this.form.id = credential.id
                this.form.platform_id = credential.platform.id;
                this.form.name = credential.name;
                this.form.synchronized = credential.synchronized;
            },

            getChildCredential(){
                let event_name = 'getSecret' + this.selected_platform_name + 'Form';
                EventBus.$emit(event_name);
            },

            update() {
                this.persistCredential(
                    'put',
                    '/credential/' + this.form.id,
                    this.form
                );
            },

            persistCredential(method, uri, form) {
                form.errors = [];

                axios[method](uri, form)
                    .then(response => {
                        EventBus.$emit('credentialChange');
                        $('#modal-platform').modal('hide');
                        this.reset()
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            form.errors = _.flatten(_.toArray(error.response.data.errors));
                        } else {
                            form.errors = ['Something went wrong. Please try again.'];
                        }
                    });

                console.log()
            }
        }
    }
</script>

<style scoped>

</style>
