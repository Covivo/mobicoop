<template>
  <div>
    <v-row>
      <v-col
        v-for="(alert, index) in alerts"
        :key="index"
        cols="12"
        md="6"
        lg="4"
      >
        <Alert
          :alert="index"
          :medium="alert"
        />
      </v-col>
    </v-row>
  </div>
</template>
<script>
import Alert from "@components/user/Alert";
export default {
  components:{
    Alert
  },
  props:{
    alerts:{
      type:Object,
      default:null
    }
  }
}
</script>