<template>
  <v-container>
    <v-row
      v-if="!loading"
      dense
    >
      <v-col
        v-for="item in items"
        :key="item.id"
        class="text-center pa-lg-10 pa-md-2"
      >
        <MSocialFeedsItem
          :i-frame-string="sanitizedIFrame(item.iFrame)"
        />
      </v-col>
    </v-row>
    <v-row
      v-else
      dense
    >
      <v-col
        v-for="n in 3"
        :key="n"
        class="text-center pa-lg-10 pa-md-2"
      >
        <v-skeleton-loader
          class="mx-auto"
          :width="width"
          :height="height"
          type="card"
        />
      </v-col>
    </v-row> 
  </v-container>
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
      items:[],
      loading:true,
      width:"100%",
      height:"600"
    }
  },
  mounted(){
    this.getArticles();
  },
  methods:{
    getArticles(){
      let params = {};
      this.loading = true;
      axios.post(this.$t("urlGetArticles"), params)
        .then(response => {
          // console.error(response.data);
          this.items = response.data;
        })
        .catch(function (error) {
          // console.log(error);
        })
        .finally(() => {
          this.loading = false;
          this.$emit("refreshActionsCompleted");
        });


      return []
    },
    sanitizedIFrame(iframe){
      return iframe.replace(/width="[0-9]+"/, 'width="'+this.width+'"').replace(/height="[0-9]+"/, 'height="'+this.height+'"');
    }
  }
}
</script>