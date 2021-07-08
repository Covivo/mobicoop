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
          style="overflow:hidden;"
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
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/social/MSocialFeeds/";
import MSocialFeedsItem from "@components/utilities/social/MSocialFeedsItem";
import maxios from "@utils/maxios";
export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    MSocialFeedsItem
  },
  data() {
    return {
      items:[],
      loading:true,
      width:"100%",
      height:"700"
    }
  },
  mounted(){
    this.getArticles();
  },
  methods:{
    getArticles(){
      let params = {};
      this.loading = true;
      maxios.post(this.$t("urlGetArticles"), params)
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