<template>
  <v-container fluid>
    <v-row no-gutters>
      <v-col
        cols="12"
        class="text-left title"
      >
        Ses avis
      </v-col>
    </v-row>
    <v-row
      v-for="review in reviews"
      :key="review.id"
      no-gutters
    >
      <v-col
        cols="12"
        class="text-left"
      >
        <Review :review="review" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import Review from "@components/utilities/Review";
export default {
  components:{
    Review
  },
  props:{
    reviews:{
      type:Array,
      default: null
    },
  }
}
</script>