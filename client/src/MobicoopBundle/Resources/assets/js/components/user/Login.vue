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
    <v-row
      justify="center"
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
        <v-card-text>
          <a
            :href="$t('urlRecovery')"
          >
            {{ $t('textRecovery') }}
          </a>
        </v-card-text>
      </v-col>
    </v-row>
    <v-row
      v-if="showFacebookLogin"
      justify="center"
      class="text-center"
    >
      <v-col class="col-4">
        <m-facebook-auth
          :app-id="facebookLoginAppId"
          @errorFacebookConnect="treatErrorMessage({'value':'errorFacebookConnect'})"
        />
      </v-col>
    </v-row>
  </v-content>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/user/Login.json";
import TranslationsClient from "@clientTranslations/components/user/Login.json";
import MFacebookAuth from '@components/user/MFacebookAuth';
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  name: "Login",
  components : {
    MFacebookAuth
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
    }
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
      else if(errorMessage.value ==="errorFacebookConnect"){
        this.errorDisplay = this.$t("errorCredentialsFacebook");
        this.loading = false;
      }
    },
  }
};
</script>