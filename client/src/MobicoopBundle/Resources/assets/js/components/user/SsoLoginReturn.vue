<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="4"
        class="text-left"
      >
        <v-card :loading="loading">
          <v-card-title>{{ $t('title') }}</v-card-title>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/SsoLoginReturn.json";

export default {
  i18n: {
    messages: Translations,
  },
  props:{
    data:{
      type: Object,
      default: null
    }
  },
  data () {
    return {
      loading:true,
      userId:null
    }
  },
  mounted(){
    this.getUser();
  },
  methods:{
    getUser(){
      axios.post("/user/sso/login/treat", {'id':this.data['id']}).then((res) => {
        console.log(res.data);
        if(res.data.length>0){
          //this.loginUser(res.data[0].id);
        }
      });          
    }
  }
}
</script>