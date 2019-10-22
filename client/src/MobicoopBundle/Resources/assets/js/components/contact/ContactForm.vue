<template>
  <v-content>
    <v-container fluid>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          sm="6"
          md="4"
          align="center"
        >
          <v-alert
            dismissible
            :value="alert.show"
            :type="alert.type"
          >
            <!--Use of span and v-html to handle multiple lines errors if needed-->
            <span v-html="alert.message" />
          </v-alert>
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
                    :label="$t('lastName.placeholder')"
                    name="familyName"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.givenName"
                    :label="$t('firstName.placeholder')"
                    name="givenName"
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
                    :items="form.demandItems"
                    :label="$t('demand.placeholder')"
                    name="demand"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-textarea
                    v-model="form.message"
                    :rules="form.messageRules"
                    :label="$t('message.placeholder') + ` *`"
                    name="message"
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
                color="success"
                rounded
                @click="validate"
              >
                {{ $t('buttons.send.label') }}
              </v-btn>
            </v-container>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import axios from "axios";
import {merge} from "lodash";
import Translations from "@translations/components/contact/ContactForm.json";
import TranslationsClient from "@clientTranslations/components/contact/ContactForm.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      loading: false,
      valid: false,
      form:{
        email: this.user && this.user.email ? this.user.email : null,
        emailRules: [
          v => !!v || this.$t("email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("email.errors.valid")
        ],
        familyName: this.user && this.user.familyName ? this.user.familyName : null,
        givenName: this.user && this.user.givenName ? this.user.givenName : null,
        demandItems: this.$t("demand.items"),
        demand: null,
        message: null,
        messageRules: [
          v => !!v || this.$t("message.errors.required"),
        ],
        website: "", // honey pot data
      },
      alert: {
        type: "success",
        message: "",
        show: false
      }
    }
  },
  methods: {
    validate() {
      const self = this;
      this.resetAlert();
      if (this.$refs.form.validate()) {
        this.loading = true;
        axios.post(this.$t('buttons.send.route'), {
          email: this.form.email,
          givenName: this.form.givenName,
          familyName: this.form.familyName,
          demand: this.form.demand,
          message: this.form.message,
          website: this.form.website // honey pot data
        })
          .then(function (response) {
            console.log(response.data);
            if (response.data && response.data.message) {
              self.alert = {
                type: "success",
                message: self.$t(response.data.message)
              };
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
              self.alert.show = true;
            }
          })
      }
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: "",
        show: false
      }
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
