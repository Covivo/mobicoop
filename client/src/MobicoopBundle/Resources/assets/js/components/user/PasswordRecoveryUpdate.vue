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
              v-model="pwd"
              :rules="pwdRules"
              :label="$t('inputs.pwd')"
              name="pwd"
              type="password"
              required
            />
            <v-text-field
              v-model="pwdConfirm"
              :rules="pwdConfirmRules"
              :label="$t('inputs.pwdConfirm')"
              name="pwdConfirm"
              type="password"
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
import axios from "axios";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/PasswordRecoveryUpdate.json";

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
      pwd:null,
      pwdRules: [
        v => !!v || this.$t("messages.errors.required"),
        v =>
          (!!v && v) === this.pwd || this.$t("messages.errors.notIdentiquals")
      ],
      pwdConfirm:null,
      pwdConfirmRules: [
        v => !!v || this.$t("form.errors.required"),
        v =>
          (!!v && v) === this.pwd || this.$t("messages.errors.notIdentiquals")
      ],
    }
  },
  methods:{
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        console.error(this.email);
        //this.loading = true;
        axios.post('/user/password/recovery/send',
          {
            password:this.pwd,
          },{
            headers:{
              'content-type': 'application/json'
            }
          })
          .then(function (response) {
            console.log(response);
          })
          .catch(function (error) {
            console.log(error);
          })
          .finally(function(){
            //this.loading = false;
          });
      }
    },
    recovery(){
          
    }
  }
};
</script>