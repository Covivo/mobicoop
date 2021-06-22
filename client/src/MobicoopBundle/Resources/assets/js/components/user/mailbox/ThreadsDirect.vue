<template>
  <v-main>
    <thread-direct
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :given-name="message.givenName"
      :short-family-name="message.shortFamilyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
      :blocker-id="message.blockerId"
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
      {{ $t("noMessage") }}
    </p>
  </v-main>
</template>
<script>

import maxios from "@utils/maxios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/mailbox/ThreadsDirect/";
import ThreadDirect from '@components/user/mailbox/ThreadDirect'

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
    ThreadDirect
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
    idMessageToSelect:{
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
    idMessageToSelect(){
      (this.idMessageToSelect) ? this.refreshSelected(this.idMessageToSelect) : '';
    },
    refreshThreads(){
      (this.refreshThreads) ? this.getThreads(this.idMessageToSelect) : '';
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  mounted(){
    this.getThreads(this.idThreadDefault);
  },
  methods:{
    emit(data){
      this.$emit("idMessageForTimeLine",data);
    },
    emitToggle(data){
      this.$emit("toggleSelected",data);
    },
    refreshSelected(idMessage){
      this.messages.forEach((item, index) => {
        if(item.idMessage == idMessage){
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
          // I'm pushing the new "virtual" thread
          if(this.newThread){
            response.data.threads.push({
              avatarsRecipient:this.newThread.avatar,
              date:moment().format(),
              shortFamilyName:this.newThread.shortFamilyName,
              givenName:this.newThread.givenName,
              idMessage:-1,
              idRecipient:this.newThread.idRecipient,
              selected: true,
              unreadMessages: 0
            });
          }
          this.messages = response.data.threads;
          (idMessageSelected) ? this.refreshSelected(idMessageSelected) : '';
          this.$emit("refreshThreadsDirectCompleted");
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
