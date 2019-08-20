<template>
  <v-content>
    <v-container
      id="scroll-target"
      style="max-height: 500px"
      class="overflow-y-auto"
      fluid
    >
      {{ originLatitude }}
      {{ date }}
    </v-container>
  </v-content>
</template>
<script>


export default {
  props: {
    originLatitude: {
      type: String,
      default: null
    },
    originLongitude: {
      type: String,
      default: null
    },
    destinationLatitude: {
      type: String,
      default: null
    },
    destinationLongitude: {
      type: String,
      default: null
    },
    date: {
      type: Date,
      default: null
    },
  },
  data : function() {
    return {
      Results : null
    };
  },
  computed : {
    //fill Results
    options () {

      return {
        duration: this.duration,
        offset: this.offset,
        easing: this.easing,
        container: this.container,
      }
    }
  }
};
</script>
