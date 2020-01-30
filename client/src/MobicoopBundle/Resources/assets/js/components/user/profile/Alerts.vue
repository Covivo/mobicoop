<template>
  <v-container>
    <v-snackbar
      v-model="snackbar"
      top
    >
      {{ snackbarText }}
      <v-btn
        color="error"
        text
        @click="snackbar = false"
      >
        Close
      </v-btn>
    </v-snackbar>
    <v-row>
      <v-col
        v-for="(alert, index) in alerts"
        :key="index"
        cols="12"
        md="6"
        lg="4"
      >
        <Alert
          :alert="alert.action"
          :medium="alert.alert"
          @changeAlert="updateAlert"
        />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import axios from "axios";
import { merge } from "lodash";
import Alert from "@components/user/profile/Alert";
import Translations from "@translations/components/user/profile/Alerts.json";
import TranslationsClient from "@clientTranslations/components/user/profile/Alerts.json";
let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  components:{
    Alert
  },
  props:{
    alerts:{
      type:Object,
      default:null
    }
  },
  data(){
    return {
      snackbar:false,
      snackbarText:null
    }
  },
  methods:{
    updateAlert(data){
      let params = {
        id:data.id,
        active:data.active
      }
      // Todo create axios method to get alerts to be able to refresh this component
      axios.post(this.$t("urlUpdate"), params)
        .then(res => {
          if(res.data.error !== undefined){
            this.snackbarText = this.$t(res.data.error);
            this.snackbar = true;
          }
        })
        .catch(function (error) {
          console.error(error);
        });

    }
  }
}
</script>