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
              ref="email"
              v-model="email"
              :rules="emailRules"
              :label="$t('inputs.email')"
              name="email"
            />
            <v-text-field
              v-model="phone"
              :label="$t('inputs.phone')"
              name="phone"
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
  </v-content>
</template>
<script>
import axios from "axios";
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
      email:null,
      phone:null,
      emailRules: [
        v => /.+@.+/.test(v) || this.$t("messages.errors.emailValid")
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
        axios.post('/user/password/recovery/send',
          {
            email:this.email,
            phone:this.phone,
          },{
            headers:{
              'content-type': 'application/json'
            }
          })
          .then(response=>{
            //console.log(response);
            if(response.data.id !== undefined){
              this.snackbarText = this.$t("snackBar.ok");
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