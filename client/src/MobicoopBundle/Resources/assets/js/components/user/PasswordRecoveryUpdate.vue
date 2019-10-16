<template>
  <v-content>
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
            //console.log(response.data);
            if(response.data.id !== undefined){
              this.snackbarText = this.$t("snackBar.ok");
              window.location.href = "/";
            }
            else{
              this.snackbarText = this.$t("snackBar.error");
            }
            this.snackbar = true;
            this.loading = false;
          })
          .catch(function (error) {
            console.log(error);
            this.snackbarText = this.$t("snackBar.error");
          });
      }
    },
    recovery(){
          
    }
  }
};
</script>