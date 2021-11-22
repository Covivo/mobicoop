<template>
  <div>
    <SsoLogin
      v-for="ssoConnection in ssoConnections"
      :key="ssoConnection.service"
      :url="ssoConnection.uri"
      :button-icon="ssoConnection.buttonIcon"
      :picto="ssoConnection.picto"
      :use-button-icon="ssoConnection.useButtonIcon"
      :service="ssoConnection.service"
    />      
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import SsoLogin from '@components/user/SsoLogin';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/SsoLogins/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
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
      maxios.post(this.$t("urlGetSsoServices"))
        .then(response => {
          this.ssoConnections = response.data;
        });      
    }      
  }
}
</script>