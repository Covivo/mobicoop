<template>
  <v-container
    fluid
    style="height: 100%"
  >
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        md="8"
        xl="6"
        align="center"
      >
        <h1>{{ $t('title') }}</h1>
      </v-col>
    </v-row>
    <div class="pt-12 mt-12">
      <v-row
        v-if="ssoConnections.length>0"
        class="text-center justify-center"
      >
        <v-col
          class="col-4"
        >
          <SsoLogin
            v-for="ssoConnection in ssoConnections"
            :key="ssoConnection.service"
            :url="ssoConnection.uri"
            :button-icon="ssoConnection.buttonIcon"
            :service="ssoConnection.service"
          />
        </v-col>
      </v-row>
      <v-row
        justify="center"
        align="center"
        class="text-center"
      >
        <v-col class="col-4">
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
            />

            <v-text-field
              id="password"
              v-model="password"
              :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
              :rules="passwordRules"
              :type="show1 ? 'text' : 'password'"
              name="password"
              :label="$t('password')"
              @click:append="show1 = !show1"
            />

            <v-btn
              :disabled="!valid"
              :loading="loading"
              color="secondary"
              type="submit"
              rounded
              @click="validate"
            >
              {{ $t('connection') }}
            </v-btn>
          </v-form>
          <v-card-text>
            <a
              :href="$t('urlRecovery')"
            >
              {{ $t('textRecovery') }}
            </a>
          </v-card-text>
          <v-card-text
            v-if="signUpLinkInConnection"
          >
            <a
              :href="$t('urlSignUp')"
              class="font-italic"
            >
              {{ $t('signUp') }}
            </a>
          </v-card-text>
        </v-col>
      </v-row>
      <v-row
        v-if="showFacebookLogin"
        justify="center"
      
        class="text-center align-start"
      >
        <v-col class="col-4">
          <m-facebook-auth
            :app-id="facebookLoginAppId"
            @errorFacebookConnect="errorFB"
          />
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>
<script>
import axios from "axios";
import { merge } from "lodash";
import {messages_fr, messages_en} from "@translations/components/user/Login/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/user/Login/";
import MFacebookAuth from '@components/user/MFacebookAuth';
import SsoLogin from '@components/user/SsoLogin';

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  name: "Login",
  components : {
    MFacebookAuth,
    SsoLogin
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
    proposalId: {
      type: Number,
      default: null
    },
    signUpLinkInConnection: {
      type: Boolean,
      default: false
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
      show1: false,
      password: "",
      passwordRules: [
        v => !!v || this.$t("passwordRequired")
      ],
      errorDisplay: "",
      action: this.proposalId ? this.$t("urlLoginResult",{"id":this.proposalId}) : this.$t("urlLogin"),
      ssoConnections:[]
    };
  },
  mounted() {
    if(this.errormessage.value !== "") this.treatErrorMessage(this.errormessage);
    //this.getSso();
    //console.log(this.$i18n.messages)
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
      this.errorDisplay = this.$t(errorMessage.value);
      this.loading = false;
    },
    getSso(){
      axios.post(this.$t("urlGetSsoServices"))
        .then(response => {
          this.ssoConnections = response.data;
        });      
    }
  }
};
</script>