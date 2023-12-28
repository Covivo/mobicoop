<template>
  <div v-if="userGratuityNotifications">
    <GratuityNotification
      v-for="userGratuityNotification in userGratuityNotifications"
      :key="userGratuityNotification.id"
      :user-gratuity-notification="userGratuityNotification"
    />
  </div>
</template>
<script>
import GratuityNotification from "@components/utilities/gratuity/GratuityNotification";
export default {
  components:{
    GratuityNotification
  },
  props:{
    userGratuityNotifications:{
      type: Array,
      default: null
    }
  },
  data () {
    return {
    }
  },
  computed:{
    template(){
      return "youpi";
    }
  }
}
</script>
