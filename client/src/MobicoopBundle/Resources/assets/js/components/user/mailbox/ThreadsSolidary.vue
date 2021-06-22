<template>
  <v-main>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :given-name="message.givenName"
      :short-family-name="message.shortFamilyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
      :origin="message.carpoolInfos.origin"
      :destination="message.carpoolInfos.destination"
      :criteria="message.carpoolInfos.criteria"
      :id-ask="message.idAsk"
      :blocked="message.blocked"
      :solidary="true"
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
      {{ $t("noSolidary") }}
    </p>
  </v-main>
</template>
<script>

import maxios from "@utils/maxios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadsSolidary/";
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
      SkeletonHidden: false
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
          // I'm pushing the new "virtual" thread
          if(this.newThread){
            response.data.threads.push({
              date:moment().format(),
              shortFamilyName:this.newThread.shortFamilyName,
              givenName:this.newThread.givenName,
              idMessage:-1,
              idRecipient:this.newThread.idRecipient
            });
          }
          (idMessageSelected) ? this.refreshSelected(idMessageSelected) : '';
          this.$emit("refreshThreadsSolidaryCompleted");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    name(givenName, shortFamilyName) {
      return givenName + " " + shortFamilyName;
    }
  }
}
</script>
