<template>
  <v-content>
    <v-container fluid />
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
    <v-layout
      justify-center
      text-center
    >
      <v-flex xs4>
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
          action="/utilisateur/connexion"
          method="POST"
        >
          <v-text-field
            v-model="email"
            :rules="emailRules"
            :label="$t('models.user.email.placeholder')"
            name="email"
            required
          />

          <v-text-field
            v-model="password"
            :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
            :rules="passwordRules"
            :type="show1 ? 'text' : 'password'"
            name="password"
            :label="$t('models.user.password.placeholder')"
            @click:append="show1 = !show1"
          />

          <v-btn
            :disabled="!valid"
            :loading="loading"
            color="success"
            type="submit"
            rounded
            @click="validate"
          >
            {{ $t('ui.button.connection') }}
          </v-btn>
        </v-form>
        <facebook-login
          class="button"
          app-id="960627727637932"
          @login="getUserData"
          @logout="onLogout"
          @get-initial-status="getUserData"
        />
        <v-card-text>
          <a
            :href="$t('urlRecovery')"
          >
            {{ $t('textRecovery') }}
          </a>
        </v-card-text>
      </v-flex>
    </v-layout>
  </v-content>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/Login.json";
import TranslationsClient from "@clientTranslations/components/user/Login.json";
import facebookLogin from 'facebook-login-vuejs';
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  name: "Login",
  components : {
    facebookLogin
  },
  props: {
    errormessage: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      valid: true,
      loading: false,
      email: "",
      emailRules: [
        v => !!v || this.$t("models.user.email.errors.required"),
        v => /.+@.+/.test(v) || this.$t("models.user.email.errors.valid")
      ],
      show1: false,
      password: "",
      passwordRules: [
        v => !!v || this.$t("models.user.password.errors.required")
      ],
      errorDisplay: "",
    };
  },
  mounted() {
    this.treatErrorMessage(this.errormessage);
  },
  methods: {
    validate() {
      if (this.$refs.form.validate()) {
        this.loading = true;
      }
    },
    treatErrorMessage(errorMessage) {
      if (errorMessage.value === "Bad credentials.") {
        this.errorDisplay = this.$t("errorCredentials");
        this.loading = false;
      }
    },
    getUseData() {
      this.FB.api('/me', 'GET', {fields: 'id,name,email' },
        userInformation => {
          console.warn("get data from fb", userInformation)
          this.personalID = userInformation.id;
          this.email = userInformation.email;
          this.name = userInformation.name;
        }
      )
    },
    sdkLoaded(payload) {
      this.isConnected = payload.isConnected
      this.FB = payload.FB
      if (this.isConnected) this.getUserData()
    },
    onLogIn() {
      this.isConnected = true
      this.getUserData()
    },
    onLogOut() {
      this.isConnected = false;
    }
  }
};
</script>