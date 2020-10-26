<template>
  <div>
    public !
  </div>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/profile/PublicProfile.json";
export default {
  i18n: {
    messages: Translations,
  },
  props:{
    userId:{
      type:Number,
      default: null
    }
  },
  data(){
    return{
      publicProfile:null,
      loading:true
    }
  },
  mounted(){
    this.getPublicProfile()
  },
  methods:{
    getPublicProfile(){
      axios.post(this.$t('getPublicProfileUri'),{'userId':this.userId})
        .then(response => {
          console.log(response.data);
          this.publicProfile = response.data;
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>