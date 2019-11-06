<template>
  <v-container>
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'success'"
      top
    >
      {{ (errorUpdate)?textSnackError:textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-row
      justify-center
      text-center
    >
      <v-col class="text-center">
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
        >
          <v-text-field
            v-model="password"
            :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
            :type="show1 ? 'text' : 'password'"
            name="password"
            :label="$t('form.newPassword')"
            required
            :rules="[passWordRules.required,passWordRules.min, passWordRules.checkUpper,passWordRules.checkLower,passWordRules.checkNumber]"
            @click:append="show1 = !show1"
          />
          <v-text-field
            v-model="passwordRepeat"
            :append-icon="show2 ? 'mdi-eye' : 'mdi-eye-off'"
            :type="show2 ? 'text' : 'password'"
            name="passwordRepeat"
            :label="$t('form.newPasswordRepeat')"
            required
            :rules="passwordRepeatRules"
            @click:append="show2 = !show2"
          />
          <v-btn
            :disabled="!valid"
            :loading="loading"
            color="secondary"
            type="button"
            rounded
            @click="validate"
          >
            {{ $t('ui.button.save') }}
          </v-btn>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import axios from "axios";

import { merge } from "lodash";
import Translations from "@translations/components/user/profile/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props: {},
  data() {
    return {
      snackbar: false,
      loading: false,
      textSnackOk: this.$t("snackBar.passwordUpdated"),
      textSnackError: this.$t("snackBar.passwordUpdateError"),
      errorUpdate: false,
      valid: true,
      password: "",
      passWordRules: {
        required:  v => !!v || this.$t("models.user.password.errors.required"),
        min: v => (v && v.length >= 8 ) || this.$t("models.user.password.errors.min"),
        checkUpper : value => {
          const pattern = /^(?=.*[A-Z]).*$/
          return pattern.test(value) || this.$t("models.user.password.errors.upper")

        },
        checkLower : value => {
          const pattern = /^(?=.*[a-z]).*$/
          return pattern.test(value) || this.$t("models.user.password.errors.lower")

        },
        checkNumber : value => {
          const pattern = /^(?=.*[0-9]).*$/
          return pattern.test(value) || this.$t("models.user.password.errors.number")

        },
      },
      passwordRepeat: "",
      passwordRepeatRules: [
        v => !!v || this.$t("form.errors.required"),
        v =>
          (!!v && v) === this.password || this.$t("form.errors.notIdentiquals")
      ],
      show1: false,
      show2: false
    };
  },
  methods: {
    validate() {
      if (this.$refs.form.validate()) {
        this.changePassword();
      }
    },
    changePassword() {
      let params = new FormData();
      params.append("password", this.password);
      this.loading = true;
      axios.post("/user/password/update", params).then(res => {
        this.errorUpdate = res.data.state;
        this.loading = false;
        this.snackbar = true;
      });
    }
  }
};
</script>