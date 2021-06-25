<template>
  <v-main>
    <v-container fluid>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          sm="8"
          md="6"
          align="center"
        >
          <v-snackbar
            v-model="snackbar"
            :color="(alert.type === 'error')?'error':'success'"
            top
          >
            <!--Use of span and v-html to handle multiple lines errors if needed-->
            <span v-html="alert.message" />
            <v-btn
              color="white"
              text
              @click="snackbar = false"
            >
              <v-icon>mdi-close-circle-outline</v-icon>
            </v-btn>
          </v-snackbar>
          <v-form
            id="formContact"
            ref="form"
            v-model="valid"
            lazy-validation
          >
            <v-container>
              <v-row>
                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.familyName"
                    :label="$t('lastName.placeholder')+` *`"
                    name="familyName"
                    required
                    :rules="form.familyNameRules"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.givenName"
                    :label="$t('firstName.placeholder')+` *`"
                    name="givenName"
                    required
                    :rules="form.givenNameRules"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.email"
                    :rules="form.emailRules"
                    :label="$t('email.placeholder') + ` *`"
                    name="email"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-select
                    v-model="form.demand"
                    :items="demandItems"
                    :rules="form.demandRules"
                    :label="$t('demand.placeholder') + ` *`"
                    name="demand"
                    required
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-textarea
                    v-model="form.message"
                    :rules="form.messageRules"
                    :label="$t('message.label') + ` *`"
                    name="message"
                    :hint="this.$t('message.hint')"
                    required
                  />
                </v-col>
                <!-- Honey pot -->
                <!-- use of HTML input to have access to required attribute -->
                <!-- use of website name is arbitrary and can be changed -->
                <v-col
                  cols="12"
                  class="noney"
                >
                  <label for="website">{{ $t('website.label') }}</label>
                  <input
                    id="website"
                    v-model="form.website"
                    type="text"
                    name="website"
                    :placeholder="$t('website.placeholder')"
                    tabindex="-1"
                    required
                  >
                </v-col>
                <!-- /Honey pot -->
              </v-row>

              <v-btn
                :disabled="!valid"
                :loading="loading"
                color="primary"
                rounded
                @click="validate"
              >
                {{ $t('buttons.send.label') }}
              </v-btn>
              
              <v-row
                class="mt-5"
              >
                <v-col cols="12">
                  <p class="text-left">
                    {{ $t('dataPolicy.text') }}
                    <a
                      :href="$t('dataPolicy.route')"
                      target="_blank"
                    >{{ $t('dataPolicy.link') }}</a>.
                  </p>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script>
import maxios from "@utils/maxios";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/contact/ContactForm/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/contact/ContactForm/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      contactTypes: null,
      snackbar: false,
      loading: false,
      valid: false,
      form:{
        email: this.user && this.user.email ? this.user.email : null,
        emailRules: [
          v => !!v || this.$t("email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("email.errors.valid")
        ],
        familyName: this.user && this.user.familyName ? this.user.familyName : null,
        familyNameRules: [
          v => !!v || this.$t("lastName.errors.required"),
        ],
        givenName: this.user && this.user.givenName ? this.user.givenName : null,
        givenNameRules: [
          v => !!v || this.$t("firstName.errors.required"),
        ],
        demand: null,
        demandRules: [
          v => !!v || this.$t("demand.errors.required"),
        ],
        message: null,
        messageRules: [
          v => !!v || this.$t("message.errors.required"),
        ],
        website: "", // honey pot data
      },
      // You need to use values corrresponding to your potential .env settings in CONTACT_TYPES 
      // By default, contact type is used    
      alert: {
        type: "success",
        message: ""
      }
    }
  },
  computed: {
    demandItems(){
      let contactItems = [];
      if(null !== this.contactTypes){
        for (let [key, value] of Object.entries(this.contactTypes)) {
          contactItems.push({text:this.$t('demand.items.'+value.demand), value:value.demand});
        }
      }
      return contactItems;
    }
  },
  mounted(){
    this.getContactItems();
  },
  methods: {
    validate() {
      const self = this;
      this.resetAlert();
      if (this.$refs.form.validate()) {
        this.loading = true;
        maxios.post(this.$t('buttons.send.route'), {
          email: this.form.email,
          givenName: this.form.givenName,
          familyName: this.form.familyName,
          demand: this.form.demand,
          message: this.form.message,
          website: this.form.website // honey pot data
        })
          .then(function (response) {
            // console.log(response.data);
            if (response.data && response.data.message) {
              self.alert = {
                type: "success",
                message: self.$t(response.data.message)
              };
              window.location.href="/";
            }
          })
          .catch(function (error) {
            console.error(error.response);
            let messages = "";
            if (error.response.data && error.response.data.errors) {
              error.response.data.errors.forEach(error => {
                messages += self.$t(error) + "<br>"
              });
            } else if (error.response.data && error.response.data.message) {
              messages = self.$t(error.response.data.message);
            }
            self.alert = {
              type: "error",
              message: messages
            };
          }).finally(function () {
            self.loading = false;
            if (self.alert.message.length > 0) {
              self.snackbar = true;
            }
          })
      }
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: ""
      }
    },
    getContactItems(){
      maxios.post(this.$t('getContactItemsUri'))
        .then(response => {
          // console.log(response.data);
          this.contactTypes = response.data;
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>

<style lang="scss" scoped>

  /* Honey pot */
  /* no display none / opacity 0 or visibility hidden to avoid bot from checking it */
  .noney {
    position: absolute;
    top: -1500px;
    left: 0;
  }
  
</style>
