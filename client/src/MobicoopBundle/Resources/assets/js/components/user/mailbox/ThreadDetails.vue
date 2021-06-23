<template>
  <v-main>
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
              <img :src="item.user.avatars[0]">
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
            :class="(item.origin==='own')?'own primary lighten-5':''"
          >
            <v-card-text
              v-html="item.text"
            />
          </v-card>
          <p
            v-if="item.divider===false && item.userDelegate"
            class="text-caption"
          >
            {{ $t('sendBy', {"name":item.userDelegate.givenName+" "+item.userDelegate.shortFamilyName}) }}
          </p>
          <span
            v-if="item.divider===true"
            class="secondary--text font-weight-bold"
          >{{ item.createdDate }}</span>
        </v-timeline-item>
      </v-timeline>
      <v-card v-else-if="(!loading && !hideNoThreadSelected) || clearClickIcon">
        <v-card-text
          class="font-italic text-subtitle-1"
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
  </v-main>
</template>
<script>

import maxios from "@utils/maxios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadDetails/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
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
    refresh:{
      type: Boolean,
      default:false
    },
    hideNoThreadSelected:{
      type: Boolean,
      default:false
    }
  },
  data(){
    return{
      textToSend:"",
      items:[],
      currentAsk:null,
      locale: localStorage.getItem("X-LOCALE"),
      boilerplate: false,
      tile: false,
      type: 'article',
      types: [],
      loading: false,
      clearClickIcon : false
    }
  },
  watch:{
    idMessage: {
      immediate: true,
      handler(newVal, oldVal) {
        if(this.idMessage!==null) this.getCompleteThread();
      }
    },
    refresh(){
      (this.refresh) ? this.getCompleteThread() : '';
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    getCompleteThread(){
      this.items = [];

      // if idMessage = -1 it means that is a "virtuel" thread. When you initiate a contact without previous message
      if(this.idMessage>-1 && this.idMessage != null){

        this.clearClickIcon = false
        this.loading = true;
        maxios.get(this.$t("urlCompleteThread",{idMessage:this.idMessage}))
          .then(response => {

            response = this.checkIfMessageIsDelete(response);

            this.loading = false;
            this.items.length = 0;

            let firstItem = {
              divider: true,
              createdDate: moment(response.data[0].createdDate).format("L")
            }
            this.items.push(firstItem);

            let currentDate = moment(response.data[0].createdDate).format("DDMMYYYY");
            response.data.forEach((item, index) => {
              item.divider = false;

              // If the date is different, push a divider
              if (moment(item.createdDate).format("DDMMYYYY") !== currentDate) {
                let divider = {
                  divider: true,
                  createdDate: moment(item.createdDate).format("L")
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

              this.emit();
            });

          })
          .catch(function (error) {
            console.log(error);
          });
      }else if (this.idMessage == -2){
        this.clearClickIcon = true
      }
      else{
        this.emit();
      }
    },
    checkIfMessageIsDelete(messages){
      let tradMessageDelete = this.$t("messageDelete");
      messages.data.forEach(function (message) {
        if (message.text == '@mobicoop2020Message_supprimer') message.text = tradMessageDelete;
      });
      return messages;


    },
    createdTime(date){
      return moment(date).format("HH:mm");
    },
    emit(){
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
.v-timeline-item.own{
  div.v-card__text{
    color: rgba(0, 0, 0);
  }
}
</style>
