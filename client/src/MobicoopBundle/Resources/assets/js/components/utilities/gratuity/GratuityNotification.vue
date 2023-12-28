<template>
  <div>
    <v-dialog
      v-if="userGratuityNotification"
      v-model="dialog"
      persistent
      max-width="600"
    >
      <v-card>
        <v-card-actions class="justify-end">
          <v-btn
            elevation="0"
            color="white"
            @click="close"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-actions>
        <v-card-text v-html="template" />
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/gratuity/GratuityNotifications/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props:{
    userGratuityNotification:{
      type: Object,
      default: null
    }
  },
  data () {
    return {
      dialog: true,
    }
  },
  computed:{
    template(){
      return this.userGratuityNotification.template;
    }
  },
  methods:{
    close(){
      this.dialog = false;
      this.tagAsNotified()
    },
    tagAsNotified(){
      // We tag these rewardSteps as notified
      maxios
        .post(this.$t('routeTagAsNotified'), {id:this.userGratuityNotification.id})
        .then(res => {
        })
    }
  }
}
</script>
