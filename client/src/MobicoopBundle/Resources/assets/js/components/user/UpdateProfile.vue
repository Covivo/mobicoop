<template>
  <v-content>
    <v-container
      fluid     
    >
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
          >
            zog zog
          </v-form>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>
<script>
import axios from 'axios';
export default {
  props: {
  },
  data(){
    return {
      valid:true
    }
  },
  methods: {
    validate () {
      if (this.$refs.form.validate()) {
        // Do spmething
      }
    }
  }
}
</script>