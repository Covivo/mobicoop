import React, { useRef, useEffect, useState } from 'react';
import { makeStyles, IconButton, LinearProgress } from '@material-ui/core';
import SendIcon from '@material-ui/icons/Send';
import { fr } from 'date-fns/locale';
import { format } from 'date-fns';
import PropTypes from 'prop-types';

const useStyles = makeStyles({
  message: {
    marginLeft: ({ ongoing }) => (ongoing ? 'auto' : 0),
    marginBottom: '25px',
    maxWidth: '350px',
    '&>p': {
      background: ({ ongoing }) => (ongoing ? '#EBEBEB' : '#05728F'),
      color: ({ ongoing }) => (ongoing ? '#333' : '#fff'),
      padding: '7px 10px',
      borderRadius: '2px',
      margin: '0',
    },
    '&>.from': {
      color: '#333',
      display: 'block',
      marginBottom: '4px',
    },
    '&>.date': {
      color: '#747474',
      padding: '7px 0 0 7px',
      fontSize: '12px',
      display: 'block',
    },
  },
  messageList: {
    listStyleType: 'none',
    padding: '10px 25px',
    maxHeight: 500,
    overflowY: 'scroll',
  },
  messageInput: {
    position: 'relative',
    padding: 10,
    '&>textarea': {
      width: '100%',
      height: '70px',
      padding: '10px 80px 10px 10px',
      boxSizing: 'border-box',
    },
  },
  sendButton: {
    top: '12px',
    right: '12px',
    position: 'absolute',
    padding: '22px',
  },
});

const Message = (props) => {
  const classes = useStyles(props);

  return (
    <li className={classes.message}>
      <span className="from">{`${props.userFamilyName} ${props.userGivenName}`}</span>
      <p>{props.text}</p>
      {props.createdDate && (
        <span className="date">
          {format(new Date(props.createdDate), "eee dd LLL HH':'mm", {
            locale: fr,
          })}
        </span>
      )}
    </li>
  );
};

export const SolidaryMessagesThread = ({ messages, onSubmit, submitting }) => {
  const classes = useStyles();
  const messageListElRef = useRef();
  const [message, setMessage] = useState('');

  useEffect(() => {
    if (messageListElRef.current) {
      messageListElRef.current.scrollTop = messageListElRef.current.scrollHeight;
    }
  }, [messages]);

  const handleSubmit = () => {
    onSubmit(message);
    setMessage('');
  };

  const handleMessageChange = (e) => {
    setMessage(e.currentTarget.value);
  };

  return (
    <>
      <ul className={classes.messageList} ref={messageListElRef}>
        {messages.map((msg) => (
          <Message key={`${msg.createdDate}-${msg.userId}`} {...msg} />
        ))}
      </ul>
      <div className={classes.messageInput}>
        {submitting && <LinearProgress />}
        <textarea value={message} onChange={handleMessageChange} />
        <IconButton
          onClick={handleSubmit}
          disabled={submitting || message.trim() === ''}
          className={classes.sendButton}
        >
          <SendIcon />
        </IconButton>
      </div>
    </>
  );
};

SolidaryMessagesThread.propTypes = {
  messages: PropTypes.array,
};

SolidaryMessagesThread.defaultProps = {
  messages: [],
};
