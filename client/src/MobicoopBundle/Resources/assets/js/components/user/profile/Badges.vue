<template>
  <v-container fluid>
    yo les badges
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl, messages_it, messages_de} from "@translations/components/user/profile/Badges/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu': messages_eu,
      'it': messages_it,
      'de': messages_de
    },
  },
  props:{
  },
  data(){
    return{
      badges: null
    }
  },
  computed:{
    badgesEarned(){
      return this.badges.filter( item => item.earned );
    },
    badgesInProgress(){
      return this.badges.filter( item => item.earningPercentage > 0 && !item.earned );
    },
    otherBadges(){
      return this.badges.filter( item => item.earningPercentage == 0 && !item.earned );
    }
  },
  mounted(){
    this.getBadgesBoard();
  },
  methods:{
    getBadgesBoard(){
      maxios
        .post(this.$t('getBadgesUrl'))
        .then(res => {
          console.log(res.data);
          this.badges = res.data.badges;
        })
        .catch(error => {
          window.location.reload();
        });
    }
  }
}
</script>      