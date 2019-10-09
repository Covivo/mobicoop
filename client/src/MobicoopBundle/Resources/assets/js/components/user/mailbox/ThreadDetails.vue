<template>
  <v-content>
    <v-container class="window-scroll">
      <v-timeline v-if="items.length>0">
        <v-timeline-item
          v-for="(item, i) in this.items"
          :key="i"
          :fil-dot="item.divider===false"
          :hide-dot="item.divider===true"
          :right="item.origin==='own'"
          :left="item.origin!=='own'"
          :idmessage="item.idMessage"
          :class="(item.divider ? 'divider' : '')+' '+item.origin"
        >     
          <template
            v-if="item.divider===false"
            v-slot:icon
          >
            <v-avatar color="secondary">
              <v-icon>mdi-account-circle</v-icon>
            </v-avatar>
          </template>
          <template
            v-if="item.divider===false"
            v-slot:opposite
          >
            <span>{{ createdTime(item.createdDate) }}</span>
          </template>
          <v-card
            v-if="item.divider===false"
            class="elevation-2 font-weight-bold"
            :class="(item.origin==='own')?'primary':''"
          >
            <v-card-text>{{ item.text }}</v-card-text>
          </v-card>
          <span
            v-if="item.divider===true"
            class="secondary--text font-weight-bold"
          >{{ item.createdDate }}</span>
        </v-timeline-item>
      </v-timeline>
      <v-card v-else>
        <v-card-text class="font-italic subtitle-1">
          Aucun fil selectionné
        </v-card-text>
      </v-card>
    </v-container>
  </v-content>
</template>
<script>
import axios from "axios";
import moment from "moment";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/ThreadDetails.json";

export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
  },
  props: {
    idMessage: {
      type: Number,
      default:null
    },
    idUser:{
      type: Number,
      default:null
    },
    iconUser:{ // Not used for now
      type: String,
      default:null
    },
    iconRecipient:{ // Not used for now
      type: String,
      default:null
    }
  },
  data(){
    return{
      textToSend:"",
      items:[],
      currentAskHistory:null,
      locale: this.$i18n.locale
    }
  },
  watch:{
    idMessage(){
      this.getCompleteThread();
    }
  },
  methods: {
    getCompleteThread(){
      axios.get("/user/messages/getCompleteThread/"+this.idMessage)
        .then(response => {
          this.items.length = 0;  

          moment.locale(this.locale);
          let firstItem = {
            divider: true,
            createdDate: moment(response.data[0].createdDate).format("ddd DD MMM YYYY")
          }
          this.items.push(firstItem);

          response.data.forEach((item, index) => {
            item.divider = false;

            // Set the origin (for display purpose)
            item.origin = ""
            if(this.idUser==item.user.id){
              item.origin = "own";
            }
            this.items.push(item);

            // Update the current AskHistory
            this.currentAskHistory = item.askHistory;

          });

        })
        .catch(function (error) {
          console.log(error);
        }); 
    },
    createdTime(date){
      return moment(date).format("HH:mm");
    },
    emit(){
      this.$emit("updateAskHistory",{currentAskHistory:this.currentAskHistory});
    }
  }
}
</script>
<style lang="scss" scoped>
.window-scroll{
  max-height:600px;
  overflow:auto;
}
</style>