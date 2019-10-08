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
      @toggleSelected="refreshSelected"
    />
  </v-content>
</template>
<script>
import axios from "axios";
import ThreadDirect from '@components/user/mailbox/ThreadDirect'
export default {
  components:{
    ThreadDirect
  },
  props: {
  },
  data(){
    return{
      messages:[]
    }
  },
  mounted(){
    axios.get("/user/messages/getDirectMessages")
      .then(response => {
        //console.error(response.data.threads);
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
    refreshSelected(data){
      this.messages.forEach((item, index) => {
        if(item.idMessage == data.idMessage){
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