<template>
  <v-content>
    <v-container class="window-scroll">
      <v-timeline
        v-if="items.length>0"
        :hidden="loading"
      >
        <v-timeline-item
          v-for="(item, i) in items"
          :key="i"
          :fil-dot="item.divider===false"
          :hide-dot="item.divider===true"
          :right="item.origin==='own'"
          :left="item.origin!=='own'"
          :idmessage="item.idMessage"
          :class="(item.divider ? 'divider' : '')+' '+(item.origin ? item.origin : '')"
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
      <v-card v-else-if="!loading">
        <v-card-text
          class="font-italic subtitle-1"
        >
          {{ $t('notThreadSelected') }}
        </v-card-text>
      </v-card>
      <v-skeleton-loader
        ref="skeleton"
        :boilerplate="boilerplate"
        :type="type"
        :tile="tile"
        class="mx-auto"
        :hidden="!loading"
      />
    </v-container>
  </v-content>
</template>
<script>
import axios from "axios";
import moment from "moment";
import Translations from "@translations/components/user/mailbox/ThreadDetails.json";

export default {
  i18n: {
    messages: Translations,
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
    },
    refresh:{
      type: Boolean,
      default:false
    }
  },
  data(){
    return{
      textToSend:"",
      items:[],
      currentAskHistory:null,
      locale: this.$i18n.locale,
      boilerplate: false,
      tile: false,
      type: 'article',
      types: [],
      loading: false
    }
  },
  watch:{
    idMessage(){
      this.getCompleteThread();
    },
    refresh(){
      (this.refresh) ? this.getCompleteThread() : '';
    }
  },
  methods: {
    getCompleteThread(){
      this.items = [];
      // if idMessage = -1 it means that is a "virtuel" thread. When you initiate a contact without previous message
      if(this.idMessage!==-1){
        this.loading = true;
        axios.get(this.$t("urlCompleteThread",{idMessage:this.idMessage}))
          .then(response => {
            this.loading = false;
            this.items.length = 0;

            moment.locale(this.locale);
            let firstItem = {
              divider: true,
              createdDate: moment(response.data[0].createdDate).format("ddd DD MMM YYYY")
            }
            this.items.push(firstItem);

            let currentDate = moment(response.data[0].createdDate).format("DDMMYYYY");
            response.data.forEach((item, index) => {
              item.divider = false;

              // If the date is different, push a divider
              if (moment(item.createdDate).format("DDMMYYYY") !== currentDate) {
                let divider = {
                  divider: true,
                  createdDate: moment(item.createdDate).format("ddd DD MMM YYYY")
                };
                currentDate = moment(item.createdDate).format("DDMMYYYY");
                this.items.push(divider);
              }

              // Set the origin (for display purpose)
              item.origin = ""
              if(this.idUser==item.user.id){
                item.origin = "own";
              }
              this.items.push(item);

              // Update the current AskHistory
              if(item.askHistory){this.currentAskHistory = item.askHistory.id}else{this.currentAskHistory=null};
              this.emit();
            });

          })
          .catch(function (error) {
            console.log(error);
          });
      }
    },
    createdTime(date){
      return moment(date).format("HH:mm");
    },
    emit(){
      this.$emit("updateAskHistory",{currentAskHistory:this.currentAskHistory});
      this.$emit("refreshCompleted");
    }
  }
}
</script>
<style lang="scss">
.window-scroll{
  max-height:600px;
  overflow:auto;
}
</style>
