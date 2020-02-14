<template>
  <v-row dense>
    <v-col
      v-for="item in items"
      :key="item.id"
      class="text-center pa-lg-10 pa-md-2"
    >
      <MSocialFeedsItem
        :i-frame-string="item.iFrame"
      />
    </v-col>
  </v-row> 
</template>
<script>
import Translations from "@translations/components/utilities/social/MSocialFeeds.json";
import MSocialFeedsItem from "@components/utilities/social/MSocialFeedsItem";
import axios from "axios";
export default {
  i18n: {
    messages: Translations
  },
  components: {
    MSocialFeedsItem
  },
  data() {
    return {
      items:[]
    }
  },
  mounted(){
    this.getArticles();
  },
  methods:{
    getArticles(){
      let params = {};
      axios.post(this.$t("urlGetArticles"), params)
        .then(response => {
          console.error(response.data);
        })
        .catch(function (error) {
          // console.log(error);
        })
        .finally(() => {
          this.$emit("refreshActionsCompleted");
        });


      return []
    }
  }
}
</script>