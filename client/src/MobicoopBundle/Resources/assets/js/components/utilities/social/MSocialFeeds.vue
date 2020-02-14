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
import MSocialFeedsItem from "@components/utilities/social/MSocialFeedsItem";
export default {
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
      return []
    }
  }
}
</script>