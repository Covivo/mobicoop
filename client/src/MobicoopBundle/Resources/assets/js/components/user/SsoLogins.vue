<template>
  <div>
    <SsoLogin
      v-for="ssoConnection in ssoConnections"
      :key="ssoConnection.service"
      :url="ssoConnection.uri"
      :button-icon="ssoConnection.buttonIcon"
      :service="ssoConnection.service"
    />      
  </div>
</template>
<script>
import axios from "axios";
import SsoLogin from '@components/user/SsoLogin';
import {messages_en, messages_fr} from "@translations/components/user/SsoLogins/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    }
  },
  components:{
    SsoLogin
  },
  data() {
    return {
      ssoConnections:[]
    };
  },
  mounted(){
    this.getSso();
  },
  methods:{
    getSso(){
      axios.post(this.$t("urlGetSsoServices"))
        .then(response => {
          this.ssoConnections = response.data;
        });      
    }      
  }
}
</script>