<template>
  <div>
    <v-alert
      v-model="showInfos"
      type="info"
    >
      <p>{{ $t("fillPublicInfos") }}</p>
      <p>{{ $t("fillRemainingFields") }}</p>
    </v-alert>
    <facebook-login
      v-if="appId && showButton"
      class="button"
      :app-id="appId"
      :login-label="labelBtn"
      @login="onLogIn"
      @logout="onLogOut"
      @sdk-loaded="sdkLoaded"
    />
  </div>
</template>
<script>

import axios from "axios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/MFacebookAuth/";
import facebookLogin from 'facebook-login-vuejs';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  name: "MFacebookAuth",
  components : {
    facebookLogin
  },
  props: {
    appId:{
      type: String,
      default:null
    },
    signUp:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      showInfos: false,
      email: "",
      isConnected: false,
      name: '',
      personalID: '',
      showButton:true
    };
  },
  computed: {
    labelBtn(){
      return (this.signUp) ? this.$t("signup") : this.$t("connection");
    }
  },
  mounted() {
  },
  methods: {
    getUserData() {
      this.showButton = false;
      this.FB.api('/me', 'GET', {fields: 'id,name,first_name,middle_name,last_name,picture,email' },
        userInformation => {

          if(this.signUp){
            // On a sign up we fill the sign up form
            this.emitForSignUp(userInformation);
            this.showInfos = true;
          }
          else{

            this.personalID = userInformation.id;
            this.email = userInformation.email;
            this.name = userInformation.name;

            axios.post(this.$t('urlFacebookConnect'),
              {
                email:this.email,
                personalID:this.personalID,
              },{
                headers:{
                  'content-type': 'application/json'
                }
              })
              .then(response => {
                if(response.data.error === undefined){
                  window.location = "/";
                }
                else{
                  this.emitError(response.data.error);
                }
              })
              .catch(function (error) {
                console.log(error);
                this.emitError();
              });

          }
        }
      )
    },
    sdkLoaded(payload) {
      this.isConnected = payload.isConnected
      this.FB = payload.FB
      if (this.isConnected) this.getUserData()
    },
    onLogIn() {
      this.isConnected = true
      this.getUserData()
    },
    onLogOut() {
      this.isConnected = false;
    },
    emitError(error){
      this.$emit("errorFacebookConnect",error);
    },
    emitForSignUp(userInformation){
      this.$emit("fillForm",userInformation);
    }
  }
};
</script>