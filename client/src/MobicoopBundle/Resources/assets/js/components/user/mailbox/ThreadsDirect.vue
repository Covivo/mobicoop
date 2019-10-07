<template>
  <v-content>
    <thread-direct
      v-for="(message, index) in messages"
      :key="index"
      :given-name="message.givenName"
      :family-name="message.familyName"
      :date="message.date"
      :id-message="message.idMessage"
      :selected-default="message.selected"
      @idMessageForTimeLine="emit"
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
        //console.error(response.data);
        this.messages = response.data;
      })
      .catch(function (error) {
        console.log(error);
      });    
  },
  methods:{
    emit(data){
      this.$emit("idMessageForTimeLine",data);
    }
  }
}
</script>