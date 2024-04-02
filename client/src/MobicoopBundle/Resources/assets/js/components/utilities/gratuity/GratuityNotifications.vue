<template>
  <div v-if="userGratuityNotifications">
    <GratuityNotification
      :user-gratuity-notification="userGratuityNotifications[0]"
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
