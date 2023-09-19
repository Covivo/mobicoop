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
              <img src="/images/avatarsDefault/square_100.svg">
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
              v-html="item.message"
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
        <warning-message
          :fraud-warning-display="fraudWarningDisplay"
          :threaded-posts="threadedPosts"
        />
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
import WarningMessage from '../../utilities/WarningMessage.vue';
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
  components: {
    WarningMessage,
  },
  props: {
    idBooking: {
      type: String,
      default:null
    },
    idUser:{
      type: Number,
      default:null
    },
    refreshBooking:{
      type: Boolean,
      default:false
    },
    hideNoThreadSelected:{
      type: Boolean,
      default:false
    },
    fraudWarningDisplay: {
      type: Boolean,
      default: false
    },
    carpoolersIdentity: {
      type: Object,
      default: () => {}
    },
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
      clearClickIcon : false,
      threadedPosts: []
    }
  },
  watch:{
    idBooking: {
      immediate: true,
      handler(newVal, oldVal) {
        if(this.idBooking!==null) this.getBookingCompleteThread();
      }
    },
    refreshBooking(){
      (this.refreshBooking) ? this.getBookingCompleteThread() : '';
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    getBookingCompleteThread(){
      this.items = [];


      this.clearClickIcon = false
      maxios.get(this.$t("urlBookingCompleteThread",{idBooking:this.idBooking}))
        .then(response => {
          this.loading = false;
          this.items.length = 0;

          let firstItem = {
            divider: true,
            createdDate: moment(response.data[0].createdDateTime.date).format("L")
          }
          this.items.push(firstItem);
          let currentDate = moment(response.data[0].createdDateTime.date).format("DDMMYYYY");

          response.data.forEach((item, index) => {

            item.divider = false;
            if (moment(item.createdDateTime.date).format("DDMMYYYY") !== currentDate) {
              let divider = {
                divider: true,
                createdDate: moment(item.createdDateTime.date).format("L")
              };
              currentDate = moment(item.createdDateTime.date).format("DDMMYYYY");
              this.items.push(divider);
            }

            if(this.idUser==item.from.externalId){
              item.origin = "own";
            }
            this.items.push(item);

            this.emit();
          });
          console.log(this.items)
        })
        .catch(function (error) {
          console.log(error);
        });
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
