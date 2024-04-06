import openai
import streamlit as st
import os
SYSTEM_PROMPT = "\n\nSystem: Your role is to understand and empathize with individuals considering foster parenting. You're here to provide reassurance, encouragement, and empowerment, helping them navigate their concerns and consider foster parenting more seriously. Aim to be a compassionate listener, offering insights and information that may alleviate worries and inspire confidence in their ability to make a positive impact in a child's life. Always respond with empathy, understanding, and positivity, while respecting the sensitivity of the subject. Personalize your responses by asking about the user's specific fears, hopes, or questions regarding foster care. This approach helps tailor your advice and support, making it more impactful and relevant to each individual's situation. Ask clarifying questions if necessary, but lean towards offering supportive and constructive responses that highlight the rewarding aspects of foster care."
#openai.api_key = os.environ['OPENAI_API_KEY']
with st.sidebar:
    openai_api_key = st.text_input("OpenAI API Key", key="chatbot_api_key", type="password")
    "[Get an OpenAI API key](https://platform.openai.com/account/api-keys)"
    "[View the source code](https://github.com/streamlit/llm-examples/blob/main/Chatbot.py)"
    "[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/streamlit/llm-examples?quickstart=1)"
st.title("What type of foster parent could you be?")
st.caption("Find out what impact you could have on a child's life. Answers and support tailored to your unique situation.")
if "messages" not in st.session_state:
    st.session_state["messages"] = [{"role": "assistant", "content": "Hello! I'm here to help you in whatever way you need, whether it be learning more about foster care, finding resources, easing your worries and doubts, or just chatting about what you're thinking. Let me know how I can best support you."}]

for msg in st.session_state.messages:
    st.chat_message(msg["role"]).write(msg["content"])

if prompt := st.chat_input():
    if not openai_api_key:
        st.info("Please add your OpenAI API key to continue.")
        st.stop()

    client = OpenAI(api_key=openai_api_key)
    st.session_state.messages.append({"role": "user", "content": prompt + SYSTEM_PROMPT})
    st.chat_message("user").write(prompt)
    response = client.chat.completions.create(model="gpt-3.5-turbo", messages=st.session_state.messages)
    msg = response.choices[0].message.content
    st.session_state.messages.append({"role": "assistant", "content": msg})
    st.chat_message("assistant").write(msg)