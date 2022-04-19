<template>
  <v-container
    fluid
  >
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        class="text-center justify-center"
      >
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <div
      class="pt-12"
    >
      <v-row
        justify="center"
        class="text-center"
      >
        <v-col class="col-5">
          <v-alert
            v-if="errorDisplay!==''"
            type="error"
            class="text-left"
          >
            {{ errorDisplay }}
          </v-alert>
          <v-form
            id="formLogin"
            ref="form"
            v-model="valid"
            lazy-validation
            :action="action"
            method="POST"
          >
            <v-text-field
              id="email"
              v-model="email"
              :rules="emailRules"
              :label="$t('email')"
              name="email"
              required
              aria-required="true"
            />

            <v-text-field
              id="password"
              v-model="password"
              :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'"
              :rules="passwordRules"
              :type="showPwd ? 'text' : 'password'"
              name="password"
              :label="$t('password')"
              aria-required="true"
              @click:append="showPwd = !showPwd"
            />

            <v-alert
              v-if="!consent"
              class="warning white--text"
            >
              <v-icon class="white--text">
                mdi-information-outline
              </v-icon> {{ $t('consent') }}
            </v-alert>
            <v-btn
              :disabled="!valid || !consent"
              :loading="loading"
              color="secondary"
              type="submit"
              rounded
              @click="validate"
            >
              {{ $t('connection') }}
            </v-btn>
          </v-form>

          <SsoLogins
            v-if="consent && ssoButtonDisplay "
            class="mt-5"
          />

          <v-card-text>
            <a
              :href="$t('urlRecovery')"
              :title="$t('textRecovery')"
              :aria-label="$t('textRecoveryAria')"
            >
              {{ $t('textRecovery') }}
            </a>
          </v-card-text>
          <v-card-text
            v-if="signUpLinkInConnection"
          >
            <a
              :href="$t('urlSignUp')"
              :title="$t('signUp')"
              :aria-label="$t('signUpAria')"
              class="font-italic"
            >
              {{ $t('signUp') }}
            </a>
          </v-card-text>
        </v-col>
      </v-row>
      <v-row
        v-if="showFacebookLogin && consentSocial"
        justify="center"
        class="text-center"
      >
        <v-col class="col-4">
          <m-facebook-auth
            :app-id="facebookLoginAppId"
            @errorFacebookConnect="errorFB"
          />
        </v-col>
      </v-row>
      <v-row
        v-else-if="showFacebookLogin"
        class="justify-center"
      >
        <v-col class="col-4 text-center">
          <v-alert
            type="info"
            class="text-left"
          >
            {{ $t('socialServicesUnavailableWithoutConsent') }}
          </v-alert>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/Login/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/Login/";
import MFacebookAuth from '@components/user/MFacebookAuth';
import SsoLogins from '@components/user/SsoLogins';

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
    }
  },
  name: "Login",
  components : {
    MFacebookAuth,
    SsoLogins
  },
  props: {
    errormessage: {
      type: Object,
      default: null
    },
    showFacebookLogin: {
      type: Boolean,
      default: false
    },
    facebookLoginAppId: {
      type: String,
      default: null
    },
    id: {
      type: Number,
      default: null
    },
    type: {
      type: String,
      default: 'default'
    },
    signUpLinkInConnection: {
      type: Boolean,
      default: false
    },
    service: {
      type: String,
      default: null
    },
    appName: {
      type: String,
      default: null
    },
    ssoButtonDisplay:{
      type: Boolean,
      default:false
    }
  },
  data() {
    return {
      valid: true,
      loading: false,
      email: "",
      emailRules: [
        v => !!v || this.$t("emailRequired"),
        v => /.+@.+/.test(v) || this.$t("emailInvalid")
      ],
      showPwd: false,
      password: "",
      passwordRules: [
        v => !!v || this.$t("passwordRequired")
      ],
      errorDisplay: ""
    };
  },
  computed: {
    action() {
      if (this.id === null && this.type !== 'publish') return this.$t("urlLogin");
      switch (this.type) {
      case 'proposal':
        return this.$t("urlLoginResult", {"id":this.id} );
      case 'event':
        return this.$t("urlLoginEvent", {"id":this.id} );
      case 'community':
        return this.$t("urlLoginCommunity", {"id":this.id} );
      case 'publish':
        return this.$t("urlLoginPublish");
      default:
        return this.$t("urlLogin");
      }
    },
    consent(){
      return this.$store.getters['up/connectionActive'];
    },
    consentSocial(){
      let social = this.$store.getters['up/social'];
      let socialCookies = this.$store.getters['up/socialCookies'];

      if(social){
        if(socialCookies && socialCookies.length > 0){
          if(socialCookies.filter((socialItem) => socialItem == 'Facebook').length > 0){
            return true;
          }
          else{
            return false;
          }
        }
        
        return true;
      }

      return social;
    }
  },
  watch: {
    getId(){
      if(this.proposalId !== null){
        this.proposalId ? this.$t("urlLoginResult",{"id":this.proposalId}) : this.$t("urlLogin")
        return this.proposalId
      } else {
        this.eventId ? this.$t("urlLoginEvent",{"id":this.eventId}) : this.$t("urlLogin")
        return this.eventId
      }
    }
  },
  mounted() {
    if(this.errormessage.value !== "") this.treatErrorMessage(this.errormessage);
  },
  methods: {
    validate() {
      if (this.$refs.form.validate()) {
        this.loading = true;
      }
    },
    errorFB(data){
      this.treatErrorMessage({'value':data})
    },
    treatErrorMessage(errorMessage) {
      this.errorDisplay = this.$t(errorMessage.value, {'appName':this.appName,'service':this.service});
      this.loading = false;
    }
  }
};
</script>