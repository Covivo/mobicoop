<template>
  <div id="calculMachine">
    <div id="result">
      <span>{{ valueDisplayed }}</span>
    </div>
    <div id="pad">
      <div class="btn" id="clear" @click="clear">C</div>
      <div class="btn" @click="handleOp('/')">/</div>
      <div class="btn" @click="handleDigit(7)">7</div>
      <div class="btn" @click="handleDigit(8)">8</div>
      <div class="btn" @click="handleDigit(9)">9</div>
      <div class="btn" @click="handleOp('x')">x</div>
      <div class="btn" @click="handleDigit(4)">4</div>
      <div class="btn" @click="handleDigit(5)">5</div>
      <div class="btn" @click="handleDigit(6)">6</div>
      <div class="btn" @click="handleOp('-')">-</div>
      <div class="btn" @click="handleDigit(1)">1</div>
      <div class="btn" @click="handleDigit(2)">2</div>
      <div class="btn" @click="handleDigit(3)">3</div>
      <div class="btn" @click="handleOp('+')">+</div>
      <div id="zero" class="btn" @click="handleDigit(0)">0</div>
      <div id="equal" class="btn" @click="handleOp('=')">=</div>
    </div>
  </div>
</template>
 
<script>
  /*
  * Ok the code under this is the main componant !!
  */
  export default {
    data () {
      return {
        currentValue: 0,
        savedValue: false,
        currentOp: false
      }
    },
    methods: {
      clear () {
        this.currentValue = 0
        this.savedValue = false
        this.currentOp = false
      },
      handleDigit (digit) {
        if (this.currentOp === '=') {
          this.savedValue = false
        }
        this.currentValue = this.currentValue * 10 + digit
      },
      handleOp (op){
        if (this.currentOp) {
          this.process()
        }
        else {
          this.savedValue = this.currentValue
        }
        this.currentValue = 0
        this.currentOp = op      
      },
      process() {
        if (this.currentOp === '+') {
          this.savedValue += this.currentValue
        }
        else if (this.currentOp === '-') {
          this.savedValue -= this.currentValue
        }
        else if (this.currentOp === 'x') {
          this.savedValue *= this.currentValue
        }
        else if (this.currentOp === '/') {
          this.savedValue /= this.currentValue
        }
        else if (this.currentOp === '=' && this.currentValue) {
          this.savedValue = this.currentValue
        }
        this.currentValue = 0
        this.currentOp = false
      }
    },
    computed: {
      valueDisplayed () {
        return this.savedValue ? 
          this.currentValue ? 
            this.currentValue : this.savedValue
          : this.currentValue
      }
    },
    name: "webcalculator"
  }
</script>
 
 <!-- & You can create style for components ONLY 😇 -->
<style scoped lang="scss">
  $size: 30px; 
  #calculMachine{
    border: 2px solid #222;
    border-radius: 4px;
    max-width: 350px;
  }
  #result{
    font-size: $size;
    width: 100%;
    height: 70px;
    box-sizing: border-box;
    display: grid;
    padding: 10px;
    justify-items: end;
    align-items: center;
    border-bottom: 2px solid #222;
  }
  #pad{
    display: grid;
    grid-template-columns: repeat(4, minmax(60px, 1fr));
    grid-gap: 10px;
    padding: 15px;
  }
  .btn{
    cursor: pointer;
    font-size: 20px;
    display: grid;
    padding: 15px 10px;
    justify-items: center;
    align-items: center;
    color: #FAFAFA;
    border: 1px solid #222;
    border-radius: 4px;
    background: #777;
  }
  #clear{
    background: steelblue;
    grid-column: 1/4;
  }
  #zero{
    grid-column: 1/4;
  }
  #equal{
    background: seagreen;
  }

</style>