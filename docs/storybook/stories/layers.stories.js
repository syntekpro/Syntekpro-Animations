export default {
  title: "Syntekpro Slider/Layers"
};

const baseSlide = {
  badge: "Featured",
  title: "Launch your campaign",
  description: "Build animated storytelling slides with precise control.",
  buttonText: "Get Started",
  caption: "Powered by Syntekpro Slider"
};

export const BadgeLayer = () => `<span class=\"sp-slide-badge\">${baseSlide.badge}</span>`;
export const TitleLayer = () => `<h3>${baseSlide.title}</h3>`;
export const DescriptionLayer = () => `<p>${baseSlide.description}</p>`;
export const ButtonLayer = () => `<a class=\"sp-slide-btn\" href=\"#\">${baseSlide.buttonText}</a>`;
export const CaptionLayer = () => `<span class=\"sp-slide-caption\">${baseSlide.caption}</span>`;
