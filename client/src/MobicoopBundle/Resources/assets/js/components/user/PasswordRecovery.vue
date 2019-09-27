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
      <v-form
        id="formLogin"
        ref="form"
        v-model="valid"
        lazy-validation
        action="/utilisateur/mot-de-passe/recuperation"
        method="POST"
      >
        <v-row>
          <v-col class="col-12">
            <v-text-field
              :rules="emailRules"
              :label="$t('inputs.email')"
              name="email"
              required
            />
            <v-text-field
              :label="$t('inputs.phone')"
              name="phone"
              required
            />
            <v-btn
              :loading="loading"
              color="success"
              type="submit"
              rounded
              @click="validate"
            >
              {{ $t('inputs.btnRecovery') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-layout>
  </v-content>
</template>
<script>
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/PasswordRecovery.json";

export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
  },
  props: {
  },
  data() {
    return {
      valid: true,
      loading:false,
      emailRules: [
        v => /.+@.+/.test(v) || this.$t("messages.errors.emailValid")
      ],
    }
  },
  methods:{
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        this.loading = true;
        this.$refs.form.submit();
      }
    },
    recovery(){
          
    }
  }
};
</script>