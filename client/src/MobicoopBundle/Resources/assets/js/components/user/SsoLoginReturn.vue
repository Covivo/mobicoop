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

import { merge } from "lodash";
import axios from "axios";
import {messages_fr, messages_en} from "@translations/components/user/SsoLoginReturn/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/user/SsoLoginReturn/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
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