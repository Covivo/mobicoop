<template>
  <div>
    <v-snackbar
      v-model="snackbar"
      top
    >
      {{ snackbarText }}
      <v-icon color="tertiary">
        mdi-information-outline
      </v-icon>
    </v-snackbar>
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
        ref="form"
        v-model="valid"
        lazy-validation
        method="POST"
      >
        <v-row>
          <v-col class="col-12">
            <v-text-field
              v-model="pwd"
              :rules="[pwdRules.required,pwdRules.min, pwdRules.checkUpper,pwdRules.checkLower,pwdRules.checkNumber]"
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
              color="primary"
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
  </div>
</template>
<script>

import axios from "axios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/PasswordRecoveryUpdate/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    token: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      valid: true,
      loading:false,
      pwd:null,
      pwdRules: {
        required:  v => !!v || this.$t("errors.required"),
        min: v => (v && v.length >= 8 ) || this.$t("errors.min"),
        checkUpper : value => {
          const pattern = /^(?=.*[A-Z]).*$/
          return pattern.test(value) || this.$t("errors.upper")

        },
        checkLower : value => {
          const pattern = /^(?=.*[a-z]).*$/
          return pattern.test(value) || this.$t("errors.lower")

        },
        checkNumber : value => {
          const pattern = /^(?=.*[0-9]).*$/
          return pattern.test(value) || this.$t("errors.number")

        },
      },
      pwdConfirm:null,
      pwdConfirmRules: [
        v => !!v || this.$t("form.errors.required"),
        v =>
          (!!v && v) === this.pwd || this.$t("messages.errors.notIdentiquals")
      ],
      snackbar:false,
      snackbarText:""
    }
  },
  methods:{
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        this.loading = true;
        
        axios.post(this.$t('urlUpdatePassword', {'token':this.token}),
          {
            password:this.pwd
          },{
            headers:{
              'content-type': 'application/json'
            }
          })
          .then(response => {
            if(response.data.id !== undefined){
              this.snackbarText = this.$t("snackBar.ok");
              window.location.href = this.$t("urlLogin");
            }
            else{
              this.snackbarText = this.$t("snackBar.error");
            }
            this.loading = false;
            this.snackbar = true;
          })
          .catch(function (error) {
            console.error(error);
            this.snackbarText = this.$t("snackBar.error");
          });
      }
    }
  }
};
</script>