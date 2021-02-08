<template>
  <div>
    <v-card flat>
      <v-row no-gutters>
        <v-col
          cols="12"
          class="success--text text-center"
        >
          Officiel 
          <v-icon class="success--text subtitle-2">
            mdi-check-circle
          </v-icon>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col
          cols="4"
          class="success--text text-center"
        >
          Sécurisé 
          <v-icon class="success--text subtitle-2">
            mdi-check-circle
          </v-icon>
        </v-col>
        <v-col
          cols="4"
          class="success--text text-center"
        >
          Privé 
          <v-icon class="success--text subtitle-2">
            mdi-check-circle
          </v-icon>
        </v-col>
        <v-col
          cols="4"
          class="success--text text-center"
        >
          Gratuit 
          <v-icon class="success--text subtitle-2">
            mdi-check-circle
          </v-icon>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col>Nombre de place : 0<br>Nombre de place PMR : 0</v-col>
      </v-row>
    </v-card>
  </div>
</template>
<script>
export default {
  props:{
    point:{
      type: Object,
      default: null
    }
  }
}
</script>