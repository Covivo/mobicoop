<template>
  <div>
    <!--SnackBar-->
    <v-snackbar
      v-model="isSnackbarOpened"
      :color="(isSent)?'success':'error'"
      top
    >
      {{ (isSent) ? this.$t('snackbar.thankyou') : this.$t('snackbar.error') }}
      <v-btn
        color="white"
        text
        @click="isSnackbarOpened = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <div v-if="!isSent">
      <v-btn
        color="error"
        rounded
        :loading="isLoading"
        @click="isDialogOpened=true"
      >
        {{ $t('button') }}
      </v-btn>
      <!--Confirmation Popup-->
      <v-dialog
        v-model="isDialogOpened"
        persistent
        max-width="500"
      >
        <v-card>
          <v-card-title class="text-h5">
            {{ cardTitle }}
          </v-card-title>
          <v-card-text
            v-html="cardContent"
          />
          <v-card-text>
            <v-form
              ref="form"
              v-model="valid"
              lazy-validation
            >
              <v-text-field
                ref="email"
                v-model="email"
                :label="$t('popup.form.email.label')"
                :rules="emailRules"
                required
              />
              <v-textarea
                ref="text"
                v-model="text"
                :label="$t('popup.form.text.label')"
                :rules="textRules"
                required
              />
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="secondary darken-1"
              text
              @click="isDialogOpened=false"
            >
              {{ $t('popup.button.cancel') }}
            </v-btn>
            <v-btn
              color="error darken-1"
              text
              :disabled="!valid || isDisable"
              @click="isDialogOpened=false; report()"
            >
              {{ $t('popup.button.report') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Report/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/Report/";
let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  props:{
    event:{
      type: Object,
      default: null
    },
    user:{
      type: Object,
      default: null
    },
    userId:{
      type: Number,
      default: null
    },
    defaultEmail:{
      type: String,
      default:null
    }
  },
  data() {
    return {
      valid: true,
      isLoading: false,
      isDialogOpened: false,
      isSnackbarOpened: false,
      snackbarText: null,
      isSent: false,
      email: this.defaultEmail,
      emailRules: [
        v => !!v || this.$t("popup.form.email.errors.required"),
        v => /.+@.+/.test(v) || this.$t("popup.form.email.errors.valid")
      ],
      text: null,
      textRules: [
        v => !!v || this.$t("popup.form.text.errors.required")
      ]
    }
  },
  computed : {
    isDisable () {
      if(!this.email || !this.text) return true;
      return false;
    },
    cardTitle(){
      if(this.event){
        return this.$t('popup.event.title');
      }
      else if(this.user){
        return this.$t('popup.user.title');
      }
      return "";
    },
    cardContent(){
      if(this.event){
        return this.$t('popup.event.content', {eventName: this.event.name});
      }
      else if(this.user){
        return this.$t('popup.user.content', {userName: this.user.givenName+' '+this.user.shortFamilyName});
      }
      return "";
    }    
  },
  methods:{
    report() {
      this.isLoading = true;

      let url = '';
      if(this.event){
        url = this.$t("routes.eventReport", {id: this.event.id});
      }
      else if(this.user){
        url = this.$t("routes.userReport", {id: this.user.id});
      }
      else if(this.userId){
        url = this.$t("routes.userReport", {id: this.userId});
      }
      else{
        return;
      }

      let params = {
        "email": this.email,
        "text": this.text
      }

      axios
        .post(url, params)
        .then(res => {
          if(200 === res.status && res.data.success) {
            this.isSent = true;
          }
          this.isSnackbarOpened = true;
          this.isLoading = false;
        });
    }
  },
}
</script>