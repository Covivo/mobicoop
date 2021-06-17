<template>
  <div>
    <v-snackbar
      v-model="snackbar"
      :color="(snackbarSuccess) ? 'success' : 'error'"
      top
    >
      {{ snackbarText }}
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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/PasswordRecovery/";

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
      snackbarText:"",
      snackbarSuccess:false
    }
  },
  methods:{
    validate() {
      event.preventDefault();
      if (this.$refs.form.validate()) {
        this.loading = true;
        axios.post(this.$t("urlPasswordRecovery"),
          {
            email:this.email,
          },{
            headers:{
              'content-type': 'application/json'
            }
          })
          .then(response=>{
            if(response.data !== null){
              this.snackbarText = this.$t("snackBar.ok");
              this.snackbarSuccess = true;
            }
            else{
              this.snackbarText = this.$t("snackBar.notfound");
            }
            this.snackbar = true;
            this.loading = false;
          })
          .catch(error=> {
            console.log(error);
            this.snackbarText = this.$t("snackBar.error");
            this.loading = false;
          });
      }
    },
    recovery(){
          
    }
  }
};
</script>