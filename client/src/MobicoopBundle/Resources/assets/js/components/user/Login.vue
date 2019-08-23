<template>
  <v-content>
    <v-container fluid />
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
        <v-card-text>
          <a
            class="secondary--text"
            :href="this.urlForgottenPassword"
          >Mot de passe oublié?</a>
        </v-card-text>
      </v-flex>
    </v-layout>
  </v-content>
</template>
<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/Login.json";
import TranslationsClient from "@clientTranslations/components/user/Login.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {
    errormessage: {
      type: String,
      default: ""
    },
    urlforgottenpassword: {
      type: String,
      default: ""
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
      urlForgottenPassword: this.urlForgottenPassword
    };
  },
  mounted() {
    this.treatErrorMessage(this.errormessage);
  },
  methods: {
    validate() {
      this.loading = true;
      if (this.$refs.form.validate()) {
        // Do something
      }
    },
    treatErrorMessage(errorMessage) {
      if (errorMessage === "Bad credentials.") {
        this.errorDisplay = this.$t("errorCredentials");
        this.loading = false;
      }
    }
  }
};
</script>