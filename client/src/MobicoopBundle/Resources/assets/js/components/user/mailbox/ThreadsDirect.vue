<template>
  <v-content>
    <thread-direct
      v-for="(message, index) in messages"
      :key="index"
      :avatar="message.avatarsRecipient"
      :given-name="message.givenName"
      :family-name="message.familyName"
      :date="message.date"
      :id-message="message.idMessage"
      :id-recipient="message.idRecipient"
      :selected-default="message.selected"
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
  </v-content>
</template>
<script>
import axios from "axios";
import moment from "moment";
import Translations from "@translations/components/user/mailbox/ThreadsDirect.json";
import ThreadDirect from '@components/user/mailbox/ThreadDirect'
export default {
  i18n: {
    messages: Translations,
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
      axios.get(this.$t("urlGet"))
        .then(response => {
          this.SkeletonHidden = true;
          // I'm pushing the new "virtual" thread
          if(this.newThread){
            response.data.threads.push({
              avatarsRecipient:this.newThread.avatar,
              date:moment().format(),
              familyName:this.newThread.familyName,
              givenName:this.newThread.givenName,
              idMessage:-1,
              idRecipient:this.newThread.idRecipient
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
    name(givenName, familyName) {
      return givenName + " " + familyName.substr(0, 1).toUpperCase() + ".";
    }
  }
}
</script>
