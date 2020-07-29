import React from 'react';
import PropTypes from 'prop-types';
import { Box } from '@material-ui/core';

const SolidaryQuestion = ({ question, children, ...rest }) => (
  <Box display="flex" flexDirection="column" mb="2rem" p={2} boxShadow={1}>
    <Box mb={1} fontWeight="fontWeightBold">
      {question}
    </Box>
    {children && React.Children.map(children, (child) => React.cloneElement(child, rest))}
  </Box>
);

SolidaryQuestion.propTypes = {
  question: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired,
};

export default SolidaryQuestion;
