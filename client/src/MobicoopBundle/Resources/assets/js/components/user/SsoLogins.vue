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
      :sso-provider="ssoConnection.ssoProvider"
      :default-buttons-active="defaultButtonsActive"
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
  props:{
    specificService:{
      type: String,
      default: ""
    },
    specificPath:{
      type: String,
      default: null
    },
    defaultButtonsActive: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      ssoConnections:[]
    };
  },
  watch:{
    ssoConnections(){
      this.ssoConnections.forEach(ssoConnections => {
        this.$store.commit('sso/setSsoButtonsActiveStatus', {
          ssoId: ssoConnections.ssoProvider,
          status: this.defaultButtonsActive
        });
      });

    }
  },
  mounted(){
    this.getSso();
  },
  methods:{
    getSso(){
      let data = {
        "service": this.specificService ? this.specificService : null,
        "path": this.specificPath
      };
      maxios.post(this.$t("urlGetSsoServices"), data)
        .then(response => {
          this.ssoConnections = response.data;
        });
    }
  }
}
</script>
