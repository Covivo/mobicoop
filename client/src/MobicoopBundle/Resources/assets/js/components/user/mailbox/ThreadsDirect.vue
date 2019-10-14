<template>
  <v-content>
    <thread-direct
      v-for="(message, index) in messages"
      :key="index"
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
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/mailbox/ThreadsDirect.json";
import ThreadDirect from '@components/user/mailbox/ThreadDirect'
export default {
  i18n: {
    messages: Translations,
    sharedMessages: CommonTranslations
  },
  components:{
    ThreadDirect
  },
  props: {
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
  mounted(){
    this.SkeletonHidden = false;
    axios.get(this.$t("urlGet"))
      .then(response => {
        //console.error(response.data.threads);
        this.SkeletonHidden = true;
        this.messages = response.data.threads;
      })
      .catch(function (error) {
        console.log(error);
      });
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
          item.selected = true;
        }
        else{
          item.selected = false;
        }
      })
    }
  }
}
</script>
