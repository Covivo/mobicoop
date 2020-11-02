<template>
  <v-container fluid>
    <v-row>
      <v-col cols="2">
        <ProfileAvatar :avatar="review.reviewer.avatar" />
      </v-col>
      <v-col>
        <v-row><v-col>{{ review.reviewer.givenName }} {{ review.reviewer.shortFamilyName }}<br>{{ review.date }}</v-col></v-row>
        <v-row><v-col>{{ review.content }}</v-col></v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import ProfileAvatar from "@components/user/profile/ProfileAvatar";
export default {
  components:{
    ProfileAvatar
  },
  props: {
    review: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
    }
  },
}
</script>