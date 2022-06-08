<template>
  <v-container fluid>
    <v-dialog
      v-model="showDialog"
      persistent
      max-width="80%"
    >
      <v-card>
        <v-card-title
          class="text-h5"
        >
          {{ $t('title', {carpooler: carpooler.givenName+' '+carpooler.shortFamilyName}) }}
        </v-card-title>
        <v-card-text>
          <PublicProfile
            :user="carpooler"
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="secondary"
            outlined
            @click="closeDialog"
          >
            {{ $t('close') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script>
import PublicProfile from "@components/user/profile/PublicProfile";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/PopupPublicProfile/";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    PublicProfile
  },
  props:{
    showProfileDialog: {
      type: Boolean,
      default: false
    },
    carpooler: {
      type: Object,
      default: null
    }
  },
  data(){
    return{
      showDialog: this.showProfileDialog
    }
  },
  watch:{
    showProfileDialog(val){
      this.showDialog = val;
    }
  },
  methods:{
    closeDialog(){
      this.showDialog = false;
      this.$emit("dialogClosed");
    }
  }
}
</script>
