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
      items:[
        {
          id:0,
          iFrame:'<iframe src="https://www.linkedin.com/embed/feed/update/urn:li:share:6634013653656051712" height="600" width="100%" frameborder="0" allowfullscreen="" title="Post intégré"></iframe>'
        },
        {
          id:1,
          iFrame:'<iframe src="https://www.linkedin.com/embed/feed/update/urn:li:share:6623207035737526272" height="600" width="100%" frameborder="0" allowfullscreen="" title="Post intégré"></iframe>'
        },
        {
          id:2,
          iFrame:'<iframe src="https://www.linkedin.com/embed/feed/update/urn:li:share:6623207035737526272" height="600" width="100%" frameborder="0" allowfullscreen="" title="Post intégré"></iframe>'
        },
      ]
    }
  },
}
</script>