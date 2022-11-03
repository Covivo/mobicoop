<template>
  <v-main>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :blocker-id="message.blockerId"
      :criteria="message.carpoolInfos.criteria"
      :date="message.date"
      :destination="message.carpoolInfos.destination"
      :given-name="message.givenName"
      :id-ask-selected="idAskSelected"
      :id-ask="message.idAsk"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :origin="message.carpoolInfos.origin"
      :selected-default="message.selected"
      :short-family-name="message.shortFamilyName"
      :unread-messages="message.unreadMessages"
      @idMessageForTimeLine="emit"
      @toggleSelected="emitToggle"
    />
    <v-skeleton-loader
      ref="skeleton"
      :boilerplate="boilerplate"
      :type="type"
      :tile="tile"
      class="mx-auto"
      :hidden="SkeletonHidden"
    />
    <p v-if="messages.length <= 0 && SkeletonHidden">
      {{ $t("noCarpool") }}
    </p>
  </v-main>
</template>
<script>

import maxios from "@utils/maxios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadsCarpool/";
import ThreadCarpool from '@components/user/mailbox/ThreadCarpool'

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
    ThreadCarpool
  },
  props: {
    idMessage: {
      type: Number,
      default: null
    },
    idThreadDefault:{
      type: Number,
      default:null
    },
    newThread:{
      type:Object,
      default:null
    },
    idAskToSelect:{
      type: Number,
      default: null
    },
    refreshThreads: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return{
      locale: localStorage.getItem("X-LOCALE"),
      messages:[],
      boilerplate: false,
      tile: false,
      type: 'list-item-avatar-three-line',
      types: [],
      SkeletonHidden: false,
      idAskSelected: this.idAskToSelect
    }
  },
  watch: {
    idAskToSelect() {
      this.idAskToSelect ? this.refreshSelected(this.idAskToSelect) : '';
    },
    refreshThreads(){
      (this.refreshThreads) ? this.getThreads(this.idMessageToSelect) : '';
    }
  },
  mounted(){
    this.getThreads(this.idAskToSelect);
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods:{
    emit(data){
      this.$emit("idMessageForTimeLine",data);
    },
    emitToggle(data){
      this.$emit("toggleSelected",data);
    },
    refreshSelected(idAsk){
      this.messages.forEach((item, index) => {
        if(item.idAsk === idAsk){
          this.$set(item, 'selected', true);
        }
        else{
          this.$set(item, 'selected', false);
        }
      })
    },
    getThreads(idMessageSelected=null){
      this.SkeletonHidden = false;
      maxios.get(this.$t("urlGet"))
        .then(response => {
          this.SkeletonHidden = true;
          this.messages = response.data.threads;
          if (this.idMessage) {
            this.selectDefaultThread();
          }
          // I'm pushing the new "virtual" thread
          if(this.newThread){
            response.data.threads.push({
              date: (this.newThread.date) ? this.newThread.date : moment().format(),
              time: (this.newThread.time) ? this.newThread.time : moment().format(),
              shortFamilyName:this.newThread.shortFamilyName,
              givenName:this.newThread.givenName,
              idMessage:-1,
              idRecipient:this.newThread.idRecipient,
              unreadMessages: 0,
              idAsk:null,
              idAskHistory:null,
              selected: true,
              avatarsRecipient:this.newThread.avatar,
              adId: this.newThread.adId,
              matchingId: this.newThread.matchingId,
              driver: this.newThread.driver,
              passenger: this.newThread.passenger,
              regular: this.newThread.regular,
              carpoolInfos:{
                askHistoryId: this.newThread.askHistoryId,
                origin:this.newThread.origin,
                destination:this.newThread.destination,
                criteria:{
                  frequency: this.newThread.frequency,
                  fromDate: this.newThread.fromDate,
                  fromTime: this.newThread.fromTime,
                  monCheck: this.newThread.monCheck,
                  tueCheck: this.newThread.tueCheck,
                  wedCheck: this.newThread.wedCheck,
                  thuCheck: this.newThread.thuCheck,
                  friCheck: this.newThread.friCheck,
                  satCheck: this.newThread.satCheck,
                  sunCheck: this.newThread.sunCheck              }
              }
            });
          }
          (idMessageSelected) ? this.refreshSelected(idMessageSelected) : '';
          this.$emit("refreshThreadsCarpoolCompleted");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    name(givenName, shortFamilyName) {
      return givenName + " " + shortFamilyName;
    },
    selectDefaultThread: function() {
      const i = this.messages.map(message => message.idMessage).indexOf(this.idMessage);

      if (i !== -1) {
        this.messages[i].selected = true;
        this.messages[i].selectedDefault = true;
        this.$emit("toggleSelected", {idAsk: this.messages[i].idAsk});
      }
    }
  }
}
</script>
