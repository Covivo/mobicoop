<template>
  <v-content>
    <thread-carpool
      v-for="(message, index) in messages"
      :key="index"
    />
  </v-content>
</template>
<script>
import axios from "axios";
import ThreadCarpool from '@components/user/mailbox/ThreadCarpool'
export default {
  components:{
    ThreadCarpool
  },
  props: {
  },
  data(){
    return{
      messages:[]
    }
  },
  mounted(){
  }
}
</script>