<template>
  <v-container>
    <v-row
      align="center"
    >
      <!-- TITLE -->
      <v-col
        align="center"
        cols="12"
      >
        <h1
          v-if="origin!=='' && destination !==''"
        >
          Covoiturages de {{ origin }} à {{ destination }}
        </h1>
        <h1
          v-else-if="origin!==''"
        >
          Covoiturages depuis {{ origin }}
        </h1>
        <h1
          v-else-if="destination!==''"
        >
          Covoiturages vers {{ destination }}
        </h1>
      </v-col> 
    </v-row>
    <v-row>
      <v-col
        align="center"
        cols="12"
      />
    </v-row>
  </v-container>
</template>

<script>

export default {
  props: {
    origin: {
      type: String,
      default: ''
    },
    destination: {
      type: String,
      default: ''
    }
  }
};
</script>